var stage = null;
var layer = null;
var pieceLayer = null;
var originX = 0;
var originY = 0;

var hexGrid = new Map();
if (typeof window !== 'undefined') window.hexGrid = hexGrid;

// -------------------------------------------------------

function getWs() {
    return window.AppWebSocket || null;
}

function getSalaId() {
    return window.CHAT_CONFIG?.salaId ?? null;
}

//-------------------------------------------------------
function initStage(width, height) {
    if (stage) {
        stage.destroy();
        stage = null;
        layer = null;
        pieceLayer = null;
    }

    hexGrid.clear();

    originX = width / 2;
    originY = height / 2;

    const container = document.getElementById('grid-layer');

    stage = new Konva.Stage({ container: 'grid-layer', width, height });

    layer = new Konva.Layer({
        clip: { x: 0, y: 0, width, height },
    });

    pieceLayer = new Konva.Layer();

    stage.add(layer);
    stage.add(pieceLayer);
}

// -------------------------------------------------------

function createHex(q, r) {
    const { x, y } = axialToPixel(q, r);
    const px = originX + x;
    const py = originY + y;

    const shape = new Konva.RegularPolygon({
        x: px,
        y: py,
        sides: 6,
        id: `hex-${q}-${r}`,
        radius: HEX_SIZE,
        fill: '#1a1a2e9d',
        rotation: 30,
        stroke: '#4a8fd980',
        strokeWidth: 1,
    });

    layer.add(shape);
    hexGrid.set(hexKey(q, r), { q, r, shape });
}

// -------------------------------------------------------

function fillGrid(canvasWidth, canvasHeight) {
    layer.destroyChildren();
    hexGrid.clear();

    const visited = new Set();
    const queue = [{ q: 0, r: 0 }];
    visited.add(hexKey(0, 0));

    while (queue.length > 0) {
        const { q, r } = queue.shift();

        const { x, y } = axialToPixel(q, r);
        const px = originX + x;
        const py = originY + y;

        if (px < -HEX_SIZE || px > canvasWidth + HEX_SIZE ||
            py < -HEX_SIZE || py > canvasHeight + HEX_SIZE) {
            continue;
        }

        createHex(q, r);

        for (const nb of axialNeighbors(q, r)) {
            const k = hexKey(nb.q, nb.r);
            if (!visited.has(k)) {
                visited.add(k);
                queue.push(nb);
            }
        }
    }

    layer.batchDraw();
}

// -------------------------------------------------------

function onPieceDragEnd(e) {
    const piece = e.target;

    const fromQ = piece.getAttr('hexQ');
    const fromR = piece.getAttr('hexR');

    const { q, r } = pixelToAxial(piece.x() - originX, piece.y() - originY);
    const target = hexGrid.get(hexKey(q, r));

    if (!target) {
        const origin = axialToPixel(fromQ, fromR);
        piece.position({ x: originX + origin.x, y: originY + origin.y });
        pieceLayer.batchDraw();
        return;
    }

    piece.position({ x: target.shape.x(), y: target.shape.y() });
    piece.setAttr('hexQ', q);
    piece.setAttr('hexR', r);
    pieceLayer.batchDraw();

    // Envia movimento
    const ws = getWs();
    const salaId = getSalaId();
    const usuarioId = window.CHAT_CONFIG?.userId;

    if (ws && salaId) {
        ws.send('/app/enviar/' + salaId, {
            acao: 'movePiecePlayer',
            usuarioId,
            salaId,
            payload: { from: { q: fromQ, r: fromR }, to: { q, r } }
        });
    }
}

function createPiece(q, r, usuarioId, color) {
    const { x, y } = axialToPixel(q, r);

    // Se não veio color (criação local), gera e broadcasta
    const isLocal = !color;
    const pieceColor = color ?? '#' + Math.floor(Math.random() * 0xFFFFFF).toString(16).padStart(6, '0');

    const piece = new Konva.Circle({
        x: originX + x,
        y: originY + y,
        radius: HEX_SIZE * 0.4,
        fill: pieceColor,
        id: `piece-${usuarioId}`,
        draggable: true,
        hexQ: q,
        hexR: r,
    });

    piece.on('dragend', onPieceDragEnd);
    pieceLayer.add(piece);
    pieceLayer.batchDraw();

    // Só broadcasta se foi criação local (não replicação de outro jogador)
    if (isLocal) {
        const ws = getWs();
        const salaId = getSalaId();

        if (ws && salaId) {
            ws.send('/app/enviar/' + salaId, {
                acao: 'piece_created',
                usuarioId,
                salaId,
                payload: { usuarioId, color: pieceColor, q, r }
            });
        }
    }

    window.gridHandlers = {

        piece_created(data) {
            const { usuarioId, color, q, r } = data.payload;
            const existing = pieceLayer.findOne(`#piece-${usuarioId}`);
            if (existing) return; // ← esse guard evita duplicata
            createPiece(q, r, usuarioId, color);
            console.log("criou")
        },

        movePiecePlayer(data) {
            const myId = String(window.CHAT_CONFIG?.userId);
            if (String(data.usuarioId) === myId) return;

            const { from, to } = data.payload;
            const piece = pieceLayer.findOne(`#piece-${data.usuarioId}`);
            const target = hexGrid.get(hexKey(to.q, to.r));

            if (!piece || !target) return;

            piece.position({ x: target.shape.x(), y: target.shape.y() });
            piece.setAttr('hexQ', to.q);
            piece.setAttr('hexR', to.r);
            pieceLayer.batchDraw();
        }
    }
}