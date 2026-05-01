window.racasMap = {};
window.classesMap = {};

export async function carregarEnums() {
    try {
        const [racasRes, classesRes] = await Promise.all([
            fetch('/enums/racas'),
            fetch('/enums/classes')
        ]);

        const racasJson = await racasRes.json();
        const classesJson = await classesRes.json();

        racasJson.data.forEach(r => {
            window.racasMap[r.constante] = r.descricao;
        });

        classesJson.data.forEach(c => {
            window.classesMap[c.constante] = c.descricao;
        });

        console.log('✅ Enums carregados');
    } catch (err) {
        console.error('❌ Erro ao carregar enums:', err);
    }
}

// top-level await permitido em module
await carregarEnums();
