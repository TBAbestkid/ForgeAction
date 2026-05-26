var stage = null;
var layer = null;
var pieceLayer = null;
var originX = 0;
var originY = 0;

var hexGrid = new Map();
if (typeof window !== 'undefined') window.hexGrid = hexGrid;

// -------------------------------------------------------

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

    const container = document.getElementById('dice-container');

    stage = new Konva.Stage({ container: 'dice-container', width, height });

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
        fill: '#1a1a2e',
        rotation: 30,
        stroke: '#4a90d9',
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

    const movePayload = { from: { q: fromQ, r: fromR }, to: { q, r } };
    console.log('move:', movePayload);
}

function createPiece(q, r) {
    const { x, y } = axialToPixel(q, r);

    const piece = new Konva.Circle({
        x: originX + x,
        y: originY + y,
        radius: HEX_SIZE * 0.4,
        fill: 'red',
        draggable: true,
        hexQ: q,
        hexR: r,
    });

    piece.on('dragend', onPieceDragEnd);
    pieceLayer.add(piece);
    pieceLayer.batchDraw();
}


document.addEventListener('DOMContentLoaded', () => {

    const btnIniciarTurno = document.getElementById('btnIniciarTurno');

    if (btnIniciarTurno) {
        btnIniciarTurno.addEventListener('click', () => {
            const w = stage ? stage.width() : 800;
            const h = stage ? stage.height() : 600;
            initStage(w, h);
            fillGrid(w, h);
            createPiece(0, 0);
        });
    }

});
