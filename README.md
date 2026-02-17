# 🐍 Snake & Ladder Pro

A feature-rich, browser-based Snake & Ladder game supporting **local multiplayer** (up to 10 players on one device) and **real-time online multiplayer** via room codes — no login required.

![Snake & Ladder Pro](https://img.shields.io/badge/Game-Snake%20%26%20Ladder-yellow?style=for-the-badge&logo=gamepad)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)
![HTML](https://img.shields.io/badge/Built%20with-HTML%2FCSS%2FJS-blue?style=for-the-badge)

---

## ✨ Features

- **Local Multiplayer** — 2–10 players sharing the same device
- **Online Multiplayer** — Create or join a room using a 4-digit room code; share a link to invite friends
- **Procedurally Generated Board** — Snakes and ladders are randomly placed each round using a seeded RNG, ensuring all players see the same board
- **Bonus Roll** — Rolling a 6 grants an extra turn; rolling 6 twice in a row does not (anti-abuse rule)
- **Animated Tokens** — Smooth CSS-animated player tokens with stacking logic when multiple players share a cell
- **Scoring System** — Points awarded based on finish rank; cumulative across rounds
- **Results Podium** — Final leaderboard shown once all players finish
- **Interim Win Toast** — Non-blocking notification when a player finishes while others continue
- **Real-time Polling** — Online mode syncs game state every few seconds via a PHP backend API
- **Responsive Design** — Playable on desktop and mobile browsers
- **Deep Link Join** — Share a URL with `?room=CODE` so friends can jump straight into a room

---

## 🚀 Getting Started

### Prerequisites

- A web server with **PHP** support (e.g., Apache, Nginx, or PHP's built-in server)
- A modern browser (Chrome, Firefox, Safari, Edge)

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-username/snake-ladder-pro.git
   cd snake-ladder-pro
   ```

2. **Serve the files**

   Using PHP's built-in server:

   ```bash
   php -S localhost:8080
   ```

   Or place the files in your web server's document root (e.g., `/var/www/html`).

3. **Open in browser**

   ```
   http://localhost:8080
   ```

### File Structure

```
snake-ladder-pro/
├── index.html      # Main game file (HTML + CSS + JS)
├── api.php         # Backend API for online multiplayer
├── README.md
└── LICENSE.md
```

---

## 🎮 How to Play

### Local Play

1. Enter your name
2. Select the number of players (2–10)
3. Click **▶ Mulai** (Start)
4. Players take turns clicking **🎲 LEMPAR DADU** (Roll Dice)

### Online Multiplayer

**Host:**

1. Enter your name
2. Select the number of player slots
3. Click **+ Buat Room** (Create Room)
4. Share the room code or link with friends
5. Click **▶ Mulai Sekarang** once at least 2 players have joined

**Guest:**

1. Enter your name
2. Paste the room code in the "Kode Room" field
3. Click **Gabung** (Join)

---

## 🎲 Game Rules

- Each player starts at position **1** and must reach exactly **100** to finish
- Roll a **6** → get a **bonus roll** (one extra turn only)
- Roll a **6** again during a bonus turn → no further bonus
- Land on a **🐍 snake head** → slide down to its tail
- Land on a **🪜 ladder base** → climb up to its top
- **Scoring:** finishing 1st earns the most points; all players can finish across multiple rounds with cumulative scores tracked

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Fonts | Google Fonts (Fredoka One, Nunito) |
| Backend | PHP (REST-style API via `api.php`) |
| Realtime | HTTP polling (client-side interval) |
| Board RNG | Linear congruential generator (seeded) |

---

## 🌐 Online API Endpoints

The frontend communicates with `api.php` using the following actions:

| Action | Method | Description |
|--------|--------|-------------|
| `create` | POST | Create a new room |
| `join` | POST | Join an existing room by code |
| `start` | POST | Host forces game to start |
| `roll` | POST | Submit a dice roll |
| `move` | POST | Submit final position after move |
| `restart` | POST | Restart the round (host only) |
| `poll` | GET | Poll for game state updates |

---

## 📱 Screenshots

> *(Add screenshots of the menu, board, and results screen here)*

---

## 🤝 Contributing

Contributions are welcome! To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m 'Add my feature'`
4. Push to the branch: `git push origin feature/my-feature`
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE.md](LICENSE.md) file for details.

---

## 🙏 Acknowledgements

- Board game concept based on the classic **Snakes and Ladders** game
- Fonts by [Google Fonts](https://fonts.google.com/)
- Dice emoji faces (⚀⚁⚂⚃⚄⚅) via Unicode
