class DiceManager {
    constructor() {
        this.diceBox = null;
        this.onRollComplete = null;
        this.isInitialized = false;
    }

    async initialize(retryCount = 0) {
        try {
            if (this.isInitialized) return;

            // Verifica se o container existe
            if (!document.getElementById('dice-container')) {
                console.warn("Container #dice-container não encontrado, aguardando...");
                if (retryCount < 5) {
                    await new Promise(resolve => setTimeout(resolve, 500));
                    return this.initialize(retryCount + 1);
                } else {
                    throw new Error("Container #dice-container não encontrado após 5 tentativas");
                }
            }

            const { default: DiceBox } = await import(
                "https://unpkg.com/@3d-dice/dice-box@1.1.3/dist/dice-box.es.min.js"
            );

            const baseConfig = {
                container: "#dice-container",
                assetPath: "/assets/",
                theme: "classic",
                scale: 25,
                velocity: { x: 3, y: 5, z: 2 },
                rotation: { x: 6, y: 8, z: 10 },
                startingPos: { x: -5, y: 5, z: 0 },
                gravity: 3,
                mass: 1,
                onRollComplete: (result) => {
                    console.log("🎲 Resultado local:", result);
                    if (this.onRollComplete) {
                        this.onRollComplete(result);
                    }
                }
            };

            this.diceBox = new DiceBox(baseConfig);

            await this.diceBox.init();
            this.isInitialized = true;
            console.log("✅ DiceManager inicializado!");
        } catch (err) {
            console.error("❌ Erro ao inicializar DiceManager:", err);
            if (retryCount < 5) {
                console.log(`Tentando inicializar novamente... (tentativa ${retryCount + 1}/5)`);
                await new Promise(resolve => setTimeout(resolve, 500));
                return this.initialize(retryCount + 1);
            }
            throw err;
        }
    }

    setRollCallback(callback) {
        this.onRollComplete = callback;
    }

    async roll(notation) {
        if (!this.isInitialized) {
            console.warn("⚠️ DiceManager não inicializado!");
            return;
        }

        try {
            await this.diceBox.roll(notation, {
                rollDelay: 50
            });
        } catch (err) {
            console.error("❌ Erro ao rolar dados:", err);
            throw err;
        }
    }
}

// Exporta instância global única
window.diceManager = new DiceManager();
