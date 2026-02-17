<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snake & Ladder Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #0d0d1a;
            --surface: #161628;
            --border: rgba(255, 255, 255, 0.08);
            --accent: #f9d71c;
            --accent2: #ff6b6b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--bg);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ====== MENU ====== */
        #menu {
            position: fixed;
            inset: 0;
            z-index: 200;
            background: radial-gradient(ellipse at 60% 40%, #1a1a3e 0%, #0d0d1a 70%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        #menu h1 {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(2rem, 8vw, 3.5rem);
            letter-spacing: 2px;
            background: linear-gradient(90deg, #f9d71c, #ff6b6b, #4d79ff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .menu-section {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px 24px;
            width: min(380px, 92vw);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu-section h3 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 2px;
        }

        .row {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        input[type=text],
        select {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 10px;
            padding: 9px 14px;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            flex: 1;
            min-width: 0;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        select option {
            background: #1a1a2e;
            color: #fff;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            transition: 0.15s;
            white-space: nowrap;
        }

        .btn:active {
            transform: scale(0.96);
        }

        .btn-primary {
            background: linear-gradient(135deg, #f9d71c, #ff9500);
            color: #000;
        }

        .btn-blue {
            background: linear-gradient(135deg, #4d79ff, #9b59b6);
            color: #fff;
        }

        .btn-green {
            background: linear-gradient(135deg, #2ecc71, #1abc9c);
            color: #fff;
        }

        .btn-red {
            background: linear-gradient(135deg, #ff4d4d, #e91e63);
            color: #fff;
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        #menuStatus {
            font-size: 13px;
            color: #f9d71c;
            min-height: 20px;
            text-align: center;
        }

        /* ====== TOP BAR ====== */
        .top-bar {
            width: 100%;
            padding: 8px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .room-badge {
            font-size: 13px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 4px 12px;
            cursor: pointer;
            user-select: none;
        }

        .room-badge:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-btns {
            display: flex;
            gap: 6px;
        }

        /* ====== SCOREBOARD ====== */
        #scoreboard {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
            padding: 10px 12px;
            width: 100%;
            max-width: 640px;
        }

        .player-card {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            min-width: 80px;
            transition: 0.2s;
            position: relative;
        }

        .player-card.active-turn {
            border-color: var(--pc);
            background: rgba(255, 255, 255, 0.1);
        }

        .player-card.finished-player {
            opacity: 0.7;
        }

        .player-card .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--pc);
            flex-shrink: 0;
            box-shadow: 0 0 6px var(--pc);
        }

        .player-card .pname {
            color: #fff;
            max-width: 70px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .player-card .pscore {
            color: var(--pc);
            margin-left: auto;
        }

        .player-card .rank-badge {
            position: absolute;
            top: -6px;
            left: -6px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #1a1a2e;
            border: 1.5px solid var(--pc);
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Bonus turn flash */
        @keyframes bonusFlash {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(249, 215, 28, 0);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(249, 215, 28, 0.35);
            }
        }

        .player-card.bonus-active {
            animation: bonusFlash 0.8s infinite;
            border-color: #f9d71c !important;
        }

        /* ====== BOARD ====== */
        #gameArea {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding-bottom: 20px;
        }

        #boardWrap {
            position: relative;
            width: min(92vw, 480px);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 8px;
            margin: 4px 0;
        }

        #board {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .cell {
            aspect-ratio: 1/1;
            font-size: clamp(6px, 1.6vw, 10px);
            color: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            padding: 2px 3px;
            position: relative;
            border: 0.5px solid rgba(255, 255, 255, 0.04);
        }

        .cell:nth-child(odd) {
            background: rgba(255, 255, 255, 0.025);
        }

        .cell:nth-child(even) {
            background: rgba(255, 255, 255, 0.055);
        }

        .snakeCell {
            background: rgba(255, 77, 77, 0.18) !important;
        }

        .ladderCell {
            background: rgba(46, 204, 113, 0.15) !important;
        }

        .cell-symbol {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -60%);
            font-size: clamp(10px, 2.5vw, 16px);
            line-height: 1;
            pointer-events: none;
        }

        /* SVG lines */
        #linesLayer {
            position: absolute;
            inset: 8px;
            pointer-events: none;
            opacity: 0.55;
            border-radius: 10px;
            overflow: hidden;
        }

        /* Tokens */
        .token-wrap {
            position: absolute;
            z-index: 20;
            transition: left 0.28s cubic-bezier(.4, 1.4, .6, 1), top 0.28s cubic-bezier(.4, 1.4, .6, 1);
            pointer-events: none;
        }

        .token-circle {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 900;
            color: #fff;
            box-shadow: 0 0 8px var(--pc), 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .token-circle.is-finished {
            opacity: 0.5;
            border-style: dashed;
        }

        /* ====== CONTROLS ====== */
        #controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            margin: 6px 0;
        }

        #diceDisplay {
            width: 64px;
            height: 64px;
            background: var(--surface);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            transition: border-color 0.2s;
        }

        #diceDisplay.rolling {
            animation: rollAnim 0.4s;
        }

        @keyframes rollAnim {
            0% {
                transform: rotate(0) scale(1.1);
            }

            25% {
                transform: rotate(-10deg) scale(0.95);
            }

            75% {
                transform: rotate(10deg) scale(1.05);
            }

            100% {
                transform: rotate(0) scale(1);
            }
        }

        /* Bonus indicator on dice */
        #bonusIndicator {
            font-size: 11px;
            font-weight: 800;
            color: #f9d71c;
            min-height: 16px;
            letter-spacing: 1px;
        }

        #rollBtn {
            font-size: 16px;
            padding: 12px 36px;
            background: linear-gradient(135deg, #f9d71c, #ff9500);
            color: #000;
            border: none;
            border-radius: 50px;
            font-family: 'Fredoka One', cursive;
            letter-spacing: 1px;
            cursor: pointer;
            transition: 0.15s;
            box-shadow: 0 4px 20px rgba(249, 215, 28, 0.35);
        }

        #rollBtn:hover:not(:disabled) {
            box-shadow: 0 6px 28px rgba(249, 215, 28, 0.5);
            transform: translateY(-1px);
        }

        #rollBtn:active:not(:disabled) {
            transform: scale(0.96);
        }

        #rollBtn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            box-shadow: none;
        }

        #turnInfo {
            font-size: 14px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.7);
            min-height: 20px;
        }

        #statusMsg {
            font-size: 12px;
            color: var(--accent);
            min-height: 16px;
        }

        /* ====== LOBBY ====== */
        #lobby {
            position: fixed;
            inset: 0;
            z-index: 150;
            background: rgba(13, 13, 26, 0.96);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }

        #lobby h2 {
            font-family: 'Fredoka One', cursive;
            font-size: 1.8rem;
        }

        #playerList {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            width: min(340px, 90vw);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .lobby-player {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            font-size: 14px;
            font-weight: 700;
        }

        .lobby-player .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .lobby-player .slot-empty {
            color: rgba(255, 255, 255, 0.3);
            font-style: italic;
        }

        #lobbyStatus {
            font-size: 13px;
            color: var(--accent);
        }

        /* ====== RESULTS PODIUM ====== */
        #resultsBanner {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 300;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.82);
            backdrop-filter: blur(6px);
        }

        .results-box {
            background: var(--surface);
            border: 2px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 28px 32px;
            text-align: center;
            width: min(360px, 92vw);
            animation: popIn 0.35s cubic-bezier(.4, 1.4, .6, 1);
        }

        @keyframes popIn {
            from {
                transform: scale(0.5);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .results-box h2 {
            font-family: 'Fredoka One', cursive;
            font-size: 1.8rem;
            margin-bottom: 16px;
        }

        .podium-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .podium-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 800;
        }

        .podium-medal {
            font-size: 22px;
            width: 30px;
            flex-shrink: 0;
        }

        .podium-name {
            flex: 1;
            text-align: left;
        }

        .podium-pts {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }

        .podium-row.rank-1 {
            background: rgba(249, 215, 28, 0.12);
            border: 1px solid rgba(249, 215, 28, 0.3);
        }

        .podium-row.rank-2 {
            background: rgba(192, 192, 192, 0.08);
            border: 1px solid rgba(192, 192, 192, 0.2);
        }

        .podium-row.rank-3 {
            background: rgba(205, 127, 50, 0.08);
            border: 1px solid rgba(205, 127, 50, 0.2);
        }

        /* Interim winner toast (not all done yet) */
        #winToast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(249, 215, 28, 0.15);
            border: 1px solid rgba(249, 215, 28, 0.4);
            border-radius: 12px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 800;
            color: #f9d71c;
            z-index: 100;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.4s;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <!-- MENU -->
    <div id="menu">
        <h1>🐍 SNAKE & LADDER 🪜</h1>
        <div class="menu-section">
            <h3>Nama Kamu</h3>
            <input type="text" id="playerName" placeholder="Masukkan nama" maxlength="20" value="Player">
        </div>
        <div class="menu-section">
            <h3>Local Play</h3>
            <div class="row">
                <label style="font-size:13px;color:rgba(255,255,255,0.6);">Jumlah pemain</label>
                <select id="localPlayerCount" style="max-width:90px;">
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                </select>
                <button class="btn btn-primary" onclick="startLocalGame()">▶ Mulai</button>
            </div>
        </div>
        <div class="menu-section">
            <h3>Online Multiplayer</h3>
            <div class="row">
                <label style="font-size:13px;color:rgba(255,255,255,0.6);">Slot</label>
                <select id="onlineMaxPlayers" style="max-width:90px;">
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                </select>
                <button class="btn btn-green" onclick="createRoom()">+ Buat Room</button>
            </div>
            <div class="row">
                <input type="text" id="joinID" placeholder="Kode Room (mis: 1234)">
                <button class="btn btn-blue" onclick="joinRoom()">Gabung</button>
            </div>
        </div>
        <p id="menuStatus"></p>
    </div>

    <!-- LOBBY -->
    <div id="lobby">
        <h2>⏳ Menunggu Pemain...</h2>
        <div id="playerList"></div>
        <p id="lobbyStatus">Bagikan kode room ke teman kamu</p>
        <div class="row" style="gap:10px;">
            <button class="btn btn-ghost" onclick="copyRoomLink()">🔗 Salin Link</button>
            <button class="btn btn-primary" id="startNowBtn" onclick="forceStart()" style="display:none;">▶ Mulai
                Sekarang</button>
        </div>
        <button class="btn btn-red" onclick="goToMenu()" style="margin-top:4px;">✕ Keluar</button>
    </div>

    <!-- RESULTS PODIUM (semua pemain selesai) -->
    <div id="resultsBanner">
        <div class="results-box">
            <div style="font-size:2.5rem;">🏆</div>
            <h2>Hasil Akhir!</h2>
            <div class="podium-list" id="podiumList"></div>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button class="btn btn-primary" onclick="closeResults()">Main Lagi</button>
                <button class="btn btn-ghost" onclick="goToMenu()">Menu</button>
            </div>
        </div>
    </div>

    <!-- INTERIM WIN TOAST -->
    <div id="winToast"></div>

    <!-- GAME -->
    <div class="top-bar">
        <span id="roomBadge" class="room-badge" onclick="copyRoomLink()" style="display:none;">📋 Room: ----</span>
        <span id="localBadge"
            style="font-family:'Fredoka One',cursive;font-size:1rem;letter-spacing:1px;color:rgba(255,255,255,0.4);">S&L
            PRO</span>
        <div class="nav-btns">
            <button class="btn btn-ghost" style="padding:5px 10px;font-size:12px;" onclick="goToMenu()">🏠</button>
            <button class="btn btn-ghost" style="padding:5px 10px;font-size:12px;" onclick="restartGame()">🔄</button>
        </div>
    </div>

    <div id="scoreboard"></div>

    <div id="gameArea">
        <div id="boardWrap">
            <div id="board"></div>
            <svg id="linesLayer" viewBox="0 0 100 100" preserveAspectRatio="none"></svg>
            <div id="tokenLayer"></div>
        </div>
        <div id="controls">
            <div id="diceDisplay">🎲</div>
            <div id="bonusIndicator"></div>
            <button id="rollBtn" onclick="handleRollClick()">🎲 LEMPAR DADU</button>
            <p id="turnInfo">Loading...</p>
            <p id="statusMsg"></p>
        </div>
    </div>

    <script>
        // ============================================================
        // CONFIG
        // ============================================================
        const API_URL = './api.php';

        const PLAYER_COLORS = [
            '#ff4d4d', '#4d79ff', '#2ecc71', '#f39c12',
            '#9b59b6', '#1abc9c', '#e91e63', '#00bcd4',
            '#ff9800', '#cddc39'
        ];
        const DICE_FACES = ['⚀', '⚁', '⚂', '⚃', '⚄', '⚅'];
        const RANK_MEDALS = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'];

        // ============================================================
        // State
        // ============================================================
        let snakes = {},
            ladders = {};
        let state = {
            players: [], // [{ index, name, position, score, finishRank, isFinished }]
            turn: 0,
            seed: 0,
            lastRoll: 0,
            lastPlayer: 0,
            bonusTurn: false, // apakah giliran saat ini adalah bonus (dapat angka 6)
            finishedCount: 0,
            status: 'waiting',
            maxPlayers: 2,
        };

        let moving = false;
        let isOnline = false;
        let myPlayerIndex = 0;
        let roomID = null;

        let pollingActive = false;
        let lastUpdatedAt = '';
        let lastKnownSeed = null;
        let lastProcessedRoll = {
            value: 0,
            player: -1
        };

        // Untuk lokal: simpan apakah roll sebelumnya adalah 6 (per pemain)
        let lastRollWasSix = {}; // { playerIndex: bool }

        // ============================================================
        // Board Grid
        // ============================================================
        const boardDiv = document.getElementById("board");
        for (let r = 9; r >= 0; r--) {
            let row = [];
            for (let c = 0; c < 10; c++)
                row.push(r % 2 === 0 ? r * 10 + c + 1 : r * 10 + (10 - c));
            row.forEach(n => {
                const cell = document.createElement("div");
                cell.className = "cell";
                cell.id = "cell" + n;
                cell.innerHTML = `<span style="position:relative;z-index:1">${n}</span>`;
                boardDiv.appendChild(cell);
            });
        }

        // ============================================================
        // Board Generation
        // ============================================================
        function generateBoard(seed) {
            const rng = (() => {
                let s = seed || Math.floor(Math.random() * 999999);
                return () => {
                    s = (s * 9301 + 49297) % 233280;
                    return s / 233280;
                };
            })();
            snakes = {};
            ladders = {};
            let tries = 0;
            while (Object.keys(snakes).length < 7 && tries++ < 200) {
                let s = Math.floor(rng() * 38) + 55,
                    e = s - (Math.floor(rng() * 38) + 10);
                if (s > e && s < 100 && e > 2 && !snakes[s] && !ladders[s]) snakes[s] = e;
            }
            tries = 0;
            while (Object.keys(ladders).length < 7 && tries++ < 200) {
                let s = Math.floor(rng() * 45) + 3,
                    e = s + (Math.floor(rng() * 28) + 10);
                if (e < 100 && !snakes[s] && !ladders[s] && !snakes[e]) ladders[s] = e;
            }
            renderBoardUI();
        }

        function renderBoardUI() {
            document.querySelectorAll(".cell-symbol").forEach(e => e.remove());
            document.querySelectorAll(".cell").forEach(c => c.classList.remove("snakeCell", "ladderCell"));
            Object.entries(snakes).forEach(([s]) => {
                const c = document.getElementById("cell" + s);
                c.classList.add("snakeCell");
                c.innerHTML += `<span class="cell-symbol">🐍</span>`;
            });
            Object.entries(ladders).forEach(([l]) => {
                const c = document.getElementById("cell" + l);
                c.classList.add("ladderCell");
                c.innerHTML += `<span class="cell-symbol">🪜</span>`;
            });
            drawLines();
        }

        function drawLines() {
            const svg = document.getElementById("linesLayer");
            svg.innerHTML = "";
            const bRect = boardDiv.getBoundingClientRect();
            [...Object.entries(snakes).map(([s, e]) => ({
                    s,
                    e,
                    c: "#ff4d4d"
                })),
                ...Object.entries(ladders).map(([s, e]) => ({
                    s,
                    e,
                    c: "#2ecc71"
                }))
            ].forEach(({
                s,
                e,
                c
            }) => {
                const p1 = getCellCenter(s),
                    p2 = getCellCenter(e);
                const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
                line.setAttribute("x1", ((p1.x - bRect.left) / bRect.width * 100).toFixed(2));
                line.setAttribute("y1", ((p1.y - bRect.top) / bRect.height * 100).toFixed(2));
                line.setAttribute("x2", ((p2.x - bRect.left) / bRect.width * 100).toFixed(2));
                line.setAttribute("y2", ((p2.y - bRect.top) / bRect.height * 100).toFixed(2));
                line.setAttribute("stroke", c);
                line.setAttribute("stroke-width", "2.5");
                line.setAttribute("stroke-linecap", "round");
                svg.appendChild(line);
            });
        }

        function getCellCenter(n) {
            const el = document.getElementById("cell" + n);
            const r = el.getBoundingClientRect();
            return {
                x: r.left + r.width / 2,
                y: r.top + r.height / 2
            };
        }

        // ============================================================
        // Tokens — create once, reposition only
        // ============================================================
        function ensureTokens() {
            const layer = document.getElementById("tokenLayer");
            state.players.forEach(p => {
                if (!document.getElementById("token" + p.index)) {
                    const wrap = document.createElement("div");
                    wrap.className = "token-wrap";
                    wrap.id = "token" + p.index;
                    const circle = document.createElement("div");
                    circle.className = "token-circle";
                    circle.style.background = PLAYER_COLORS[p.index];
                    circle.style.setProperty('--pc', PLAYER_COLORS[p.index]);
                    circle.textContent = p.index + 1;
                    wrap.appendChild(circle);
                    layer.appendChild(wrap);
                }
                // Update finished style
                const circle = document.querySelector(`#token${p.index} .token-circle`);
                if (circle) circle.classList.toggle("is-finished", p.isFinished);
            });
        }

        function renderTokens(skipIdx = -1) {
            ensureTokens();
            const wRect = document.getElementById("boardWrap").getBoundingClientRect();
            const countByCell = {};
            state.players.forEach(p => {
                if (p.index === skipIdx) return;
                countByCell[p.position] = (countByCell[p.position] || 0) + 1;
            });
            const slotByCell = {};
            state.players.forEach(p => {
                if (p.index === skipIdx) return;
                const cellEl = document.getElementById("cell" + p.position);
                if (!cellEl) return;
                const slot = slotByCell[p.position] = (slotByCell[p.position] || 0);
                slotByCell[p.position]++;
                const cnt = countByCell[p.position];
                const c = getCellCenter(p.position);
                const cellW = cellEl.getBoundingClientRect().width;
                const angle = cnt > 1 ? (slot / cnt) * 2 * Math.PI : 0;
                const radius = cnt > 1 ? cellW * 0.22 : 0;
                const tx = c.x - wRect.left + Math.cos(angle) * radius - 9;
                const ty = c.y - wRect.top + Math.sin(angle) * radius - 9;
                const wrap = document.getElementById("token" + p.index);
                if (wrap) {
                    wrap.style.left = tx + "px";
                    wrap.style.top = ty + "px";
                }
            });
        }

        function moveTokenSmooth(pIdx, newPos) {
            const wrap = document.getElementById("token" + pIdx);
            if (!wrap) return;
            const wRect = document.getElementById("boardWrap").getBoundingClientRect();
            const c = getCellCenter(newPos);
            wrap.style.left = (c.x - wRect.left - 9) + "px";
            wrap.style.top = (c.y - wRect.top - 9) + "px";
        }

        function snapToken(pIdx, pos) {
            const wrap = document.getElementById("token" + pIdx);
            if (!wrap) return;
            wrap.style.transition = 'none';
            moveTokenSmooth(pIdx, pos);
            void wrap.offsetWidth;
            wrap.style.transition = '';
        }

        // ============================================================
        // Scoreboard
        // ============================================================
        function renderScoreboard() {
            const sb = document.getElementById("scoreboard");
            sb.innerHTML = "";
            // Sort: yang sudah finish tampil dulu urut rank, lalu yang belum
            const sorted = [...state.players].sort((a, b) => {
                if (a.isFinished && b.isFinished) return a.finishRank - b.finishRank;
                if (a.isFinished) return -1;
                if (b.isFinished) return 1;
                return a.index - b.index;
            });
            sorted.forEach(p => {
                const isActive = state.turn === p.index;
                const isBonus = isActive && state.bonusTurn;
                const card = document.createElement("div");
                card.className = "player-card" +
                    (isActive && !p.isFinished ? " active-turn" : "") +
                    (p.isFinished ? " finished-player" : "") +
                    (isBonus ? " bonus-active" : "");
                card.style.setProperty('--pc', PLAYER_COLORS[p.index]);
                card.innerHTML = `
            ${p.finishRank > 0 ? `<div class="rank-badge">${RANK_MEDALS[p.finishRank - 1]}</div>` : ''}
            <div class="dot" style="background:${PLAYER_COLORS[p.index]};box-shadow:0 0 6px ${PLAYER_COLORS[p.index]}"></div>
            <span class="pname">${escHtml(p.name)}</span>
            <span class="pscore" style="color:${PLAYER_COLORS[p.index]}">★${p.score}</span>
        `;
                sb.appendChild(card);
            });
        }

        // ============================================================
        // Roll + Move Logic
        // ============================================================
        async function handleRollClick() {
            if (moving) return;
            if (isOnline && state.turn !== myPlayerIndex) {
                setStatus("⛔ Bukan giliran kamu!");
                return;
            }
            const currentP = state.players.find(p => p.index === state.turn);
            if (!currentP || currentP.isFinished) {
                // Seharusnya tidak terjadi, tapi safeguard
                advanceTurn();
                renderUI();
                return;
            }

            const dice = Math.floor(Math.random() * 6) + 1;
            document.getElementById("diceDisplay").classList.add("rolling");
            document.getElementById("diceDisplay").textContent = DICE_FACES[dice - 1];
            setTimeout(() => document.getElementById("diceDisplay").classList.remove("rolling"), 400);

            if (isOnline) {
                setStatus("Mengirim...");
                const res = await apiCall('roll', {
                    roomCode: roomID,
                    playerIndex: myPlayerIndex,
                    diceValue: dice
                });
                if (!res.success) {
                    setStatus("❌ " + res.error);
                    return;
                }
                setStatus("");
                // bonusTurn dari server: kalau true → giliran tetap pada kita setelah move
                const willGetBonus = res.room.bonusTurn && res.room.lastPlayer === myPlayerIndex;
                await executeMove(dice, myPlayerIndex, true, willGetBonus);
                // Setelah move, sync turn dari server
                state.turn = res.room.turn;
                state.bonusTurn = res.room.bonusTurn;
            } else {
                const curTurn = state.turn;
                const prevWasSix = lastRollWasSix[curTurn] || false;

                // Bonus: dapat 6 pertama → beri giliran ekstra. Dapat 6 lagi saat bonus → tidak bonus lagi.
                const grantBonus = (dice === 6 && !prevWasSix);
                lastRollWasSix[curTurn] = (dice === 6 && !prevWasSix); // simpan untuk cek berikutnya
                // Jika dapat 6 saat bonus → hapus status bonus
                if (dice === 6 && prevWasSix) lastRollWasSix[curTurn] = false;

                if (dice !== 6) lastRollWasSix[curTurn] = false;

                await executeMove(dice, curTurn, false, grantBonus);
                if (!moving) {
                    if (grantBonus) {
                        // Giliran tetap — update bonusTurn state
                        state.bonusTurn = true;
                        setStatus(`🎲 ${state.players.find(p => p.index === curTurn)?.name} dapat angka 6 — ROLL LAGI!`);
                    } else {
                        state.bonusTurn = false;
                        advanceTurn();
                    }
                    renderUI();
                }
            }
        }

        async function executeMove(dice, pIdx, sendToServer, grantBonus = false) {
            moving = true;
            const player = state.players.find(p => p.index === pIdx);
            if (!player) {
                moving = false;
                return;
            }

            let pos = player.position;
            let target = pos + dice;
            let eventType = 'normal';

            if (target <= 100) {
                for (let i = pos + 1; i <= target; i++) {
                    player.position = i;
                    moveTokenSmooth(pIdx, i);
                    renderUI(pIdx);
                    await sleep(220);
                }
                if (snakes[player.position]) {
                    await sleep(280);
                    player.position = snakes[player.position];
                    moveTokenSmooth(pIdx, player.position);
                    eventType = 'snake';
                    setStatus("🐍 Kena ular! Turun...");
                    await sleep(600);
                } else if (ladders[player.position]) {
                    await sleep(280);
                    player.position = ladders[player.position];
                    moveTokenSmooth(pIdx, player.position);
                    eventType = 'ladder';
                    setStatus("🪜 Naik tangga!");
                    await sleep(600);
                }
            }

            setStatus("");

            if (sendToServer) {
                const isWin = player.position === 100;
                await apiCall('move', {
                    roomCode: roomID,
                    playerIndex: pIdx,
                    finalPos: player.position,
                    diceValue: dice,
                    eventType: isWin ? 'win' : eventType
                });
            }

            // Render lengkap setelah move selesai
            renderUI();

            // Cek finish
            if (player.position === 100 && !player.isFinished) {
                player.isFinished = true;
                state.finishedCount++;
                player.finishRank = state.finishedCount;

                // Poin = (maxPlayers - rank + 1), minimal 1
                player.score += Math.max(1, state.maxPlayers - player.finishRank + 1);

                const allDone = state.players.every(p => p.isFinished);

                if (allDone) {
                    // Semua selesai → tampilkan hasil akhir
                    await sleep(400);
                    showResults();
                    if (!isOnline) {
                        await sleep(300);
                        resetRound();
                    }
                } else {
                    // Masih ada yang bermain → tampilkan toast sementara
                    showWinToast(player.name, player.finishRank);
                    // Pemain ini tetap lanjut ke step bonus/turn-advance di luar
                }
            }

            moving = false;
        }

        // ============================================================
        // Turn Management (Local)
        // ============================================================
        function advanceTurn() {
            const notFinished = state.players.filter(p => !p.isFinished);
            if (!notFinished.length) return;
            let next = (state.turn + 1) % state.players.length;
            let tries = 0;
            while (state.players[next]?.isFinished && tries++ < state.players.length) {
                next = (next + 1) % state.players.length;
            }
            state.turn = next;
        }

        // ============================================================
        // UI
        // ============================================================
        function renderUI(skipIdx = -1) {
            renderScoreboard();
            renderTokens(skipIdx);

            const currentP = state.players.find(p => p.index === state.turn);
            const myTurn = !isOnline || state.turn === myPlayerIndex;
            const canRoll = myTurn && !moving && currentP && !currentP.isFinished;

            let turnText = currentP ? `${currentP.name}'s Turn` : "";
            if (isOnline) turnText += myTurn ? " — GILIRAN KAMU!" : " — menunggu...";

            document.getElementById("turnInfo").textContent = turnText;
            document.getElementById("rollBtn").disabled = !canRoll;

            // Bonus indicator
            const bonusEl = document.getElementById("bonusIndicator");
            if (state.bonusTurn && (myTurn || !isOnline)) {
                bonusEl.textContent = "🎲 BONUS ROLL — Dapat angka 6!";
            } else if (state.bonusTurn) {
                const bonusPlayer = state.players.find(p => p.index === state.turn);
                bonusEl.textContent = bonusPlayer ? `${bonusPlayer.name} dapat bonus roll!` : "";
            } else {
                bonusEl.textContent = "";
            }
        }

        function setStatus(msg) {
            document.getElementById("statusMsg").textContent = msg;
        }

        // ============================================================
        // Toast (interim winner)
        // ============================================================
        function showWinToast(name, rank) {
            const toast = document.getElementById("winToast");
            toast.textContent = `${RANK_MEDALS[rank - 1]} ${name} finish posisi #${rank}! Game terus...`;
            toast.style.opacity = "1";
            setTimeout(() => toast.style.opacity = "0", 3000);
        }

        // ============================================================
        // Results Podium (semua selesai)
        // ============================================================
        function showResults() {
            const sorted = [...state.players].sort((a, b) => a.finishRank - b.finishRank);
            const list = document.getElementById("podiumList");
            list.innerHTML = "";
            sorted.forEach(p => {
                const row = document.createElement("div");
                row.className = `podium-row rank-${p.finishRank}`;
                row.innerHTML = `
            <div class="podium-medal">${RANK_MEDALS[p.finishRank - 1]}</div>
            <div class="podium-name" style="color:${PLAYER_COLORS[p.index]}">${escHtml(p.name)}</div>
            <div class="podium-pts">★${p.score} poin</div>
        `;
                list.appendChild(row);
            });
            document.getElementById("resultsBanner").style.display = "flex";
        }

        function closeResults() {
            document.getElementById("resultsBanner").style.display = "none";
            if (isOnline && myPlayerIndex === 0) restartGame();
            else if (!isOnline) resetRound();
        }

        function resetRound() {
            state.players.forEach(p => {
                p.position = 1;
                p.isFinished = false;
                p.finishRank = 0;
            });
            state.turn = 0;
            state.finishedCount = 0;
            state.bonusTurn = false;
            lastRollWasSix = {};
            moving = false;
            const seed = Math.floor(Math.random() * 999999);
            generateBoard(seed);
            state.players.forEach(p => snapToken(p.index, 1));
            renderUI();
        }

        function escHtml(s) {
            return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }

        // ============================================================
        // Local Game Start
        // ============================================================
        function startLocalGame() {
            const count = parseInt(document.getElementById("localPlayerCount").value);
            const myName = document.getElementById("playerName").value.trim() || "Player 1";
            isOnline = false;
            myPlayerIndex = 0;
            moving = false;
            lastRollWasSix = {};
            state = {
                players: Array.from({
                    length: count
                }, (_, i) => ({
                    index: i,
                    name: i === 0 ? myName : `Player ${i + 1}`,
                    position: 1,
                    score: 0,
                    finishRank: 0,
                    isFinished: false
                })),
                turn: 0,
                seed: 0,
                lastRoll: 0,
                lastPlayer: 0,
                bonusTurn: false,
                finishedCount: 0,
                status: 'playing',
                maxPlayers: count
            };
            const seed = Math.floor(Math.random() * 999999);
            generateBoard(seed);
            ensureTokens();
            state.players.forEach(p => snapToken(p.index, 1));
            document.getElementById("menu").style.display = "none";
            renderUI();
        }

        // ============================================================
        // API Helpers
        // ============================================================
        async function apiCall(action, body) {
            try {
                const res = await fetch(`${API_URL}?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                });
                return res.json();
            } catch (e) {
                return {
                    success: false,
                    error: e.message
                };
            }
        }
        async function apiGet(action, params = {}) {
            try {
                const qs = new URLSearchParams({
                    action,
                    ...params
                }).toString();
                return (await fetch(`${API_URL}?${qs}`)).json();
            } catch (e) {
                return {
                    success: false,
                    error: e.message
                };
            }
        }

        // ============================================================
        // Online: Create / Join
        // ============================================================
        async function createRoom() {
            const name = document.getElementById("playerName").value.trim() || "Player 1";
            const max = parseInt(document.getElementById("onlineMaxPlayers").value);
            document.getElementById("menuStatus").textContent = "Membuat room...";
            const res = await apiCall('create', {
                playerName: name,
                maxPlayers: max
            });
            if (!res.success) {
                document.getElementById("menuStatus").textContent = "❌ " + res.error;
                return;
            }
            roomID = res.room.roomCode;
            myPlayerIndex = 0;
            isOnline = true;
            lastUpdatedAt = res.room.updatedAt;
            lastKnownSeed = res.room.seed;
            applyRoomState(res.room);
            document.getElementById("menu").style.display = "none";
            showLobby();
            startPolling();
        }

        async function joinRoom() {
            const code = document.getElementById("joinID").value.trim();
            const name = document.getElementById("playerName").value.trim() || "Player";
            if (!code) {
                alert("Masukkan kode room!");
                return;
            }
            document.getElementById("menuStatus").textContent = "Bergabung...";
            const res = await apiCall('join', {
                roomCode: code,
                playerName: name
            });
            if (!res.success) {
                document.getElementById("menuStatus").textContent = "❌ " + res.error;
                return;
            }
            roomID = code;
            myPlayerIndex = res.assignedIndex;
            isOnline = true;
            lastUpdatedAt = res.room.updatedAt;
            lastKnownSeed = res.room.seed;
            applyRoomState(res.room);
            document.getElementById("menu").style.display = "none";
            if (res.room.status === 'playing') hideLobby();
            else showLobby();
            startPolling();
        }

        async function forceStart() {
            if (myPlayerIndex !== 0) return;
            const res = await apiCall('start', {
                roomCode: roomID,
                playerIndex: 0
            });
            if (res.success) {
                applyRoomState(res.room);
                hideLobby();
            } else alert(res.error);
        }

        // ============================================================
        // Online: Polling
        // ============================================================
        function startPolling() {
            if (pollingActive) return;
            pollingActive = true;
            pollLoop();
        }
        async function pollLoop() {
            while (pollingActive) {
                try {
                    const res = await apiGet('poll', {
                        roomCode: roomID,
                        since: lastUpdatedAt
                    });
                    if (res.success && res.room && res.room.updatedAt !== lastUpdatedAt) {
                        lastUpdatedAt = res.room.updatedAt;
                        await handleRemoteUpdate(res.room);
                    }
                } catch (e) {
                    await sleep(2000);
                }
            }
        }

        async function handleRemoteUpdate(room) {
            const oldStatus = state.status;

            if (room.seed !== lastKnownSeed) {
                lastKnownSeed = room.seed;
                generateBoard(room.seed);
                state.players.forEach(p => {
                    p.position = 1;
                    p.isFinished = false;
                    p.finishRank = 0;
                    snapToken(p.index, 1);
                });
                state.finishedCount = 0;
                state.bonusTurn = false;
            }

            if (oldStatus === 'waiting' && room.status === 'playing') hideLobby();

            const isOpponentRoll = room.lastPlayer !== myPlayerIndex;
            const isNewRoll = room.lastRoll > 0 &&
                (room.lastRoll !== lastProcessedRoll.value || room.lastPlayer !== lastProcessedRoll.player);

            if (isOpponentRoll && isNewRoll && !moving) {
                lastProcessedRoll = {
                    value: room.lastRoll,
                    player: room.lastPlayer
                };

                document.getElementById("diceDisplay").textContent = DICE_FACES[room.lastRoll - 1];
                const willBonus = room.bonusTurn && room.lastPlayer === room.turn;
                await executeMove(room.lastRoll, room.lastPlayer, false, willBonus);

                // Sync posisi dari server setelah animasi
                room.players.forEach(sp => {
                    const lp = state.players.find(p => p.index === sp.index);
                    if (lp) {
                        lp.score = sp.score;
                        lp.isFinished = sp.isFinished;
                        lp.finishRank = sp.finishRank;
                        if (lp.position !== sp.position) {
                            lp.position = sp.position;
                            snapToken(sp.index, sp.position);
                        }
                    }
                });
            } else {
                applyRoomState(room);
            }

            state.turn = room.turn;
            state.bonusTurn = room.bonusTurn;
            state.finishedCount = room.finishedCount;
            renderUI();

            if (room.status === 'finished') showResults();
        }

        // ============================================================
        // Apply Room State
        // ============================================================
        function applyRoomState(room) {
            state.turn = room.turn;
            state.status = room.status;
            state.maxPlayers = room.maxPlayers;
            state.lastRoll = room.lastRoll;
            state.lastPlayer = room.lastPlayer;
            state.bonusTurn = room.bonusTurn;
            state.finishedCount = room.finishedCount;

            room.players.forEach(sp => {
                let lp = state.players.find(p => p.index === sp.index);
                if (!lp) {
                    lp = {
                        index: sp.index,
                        name: sp.name,
                        position: 1,
                        score: 0,
                        finishRank: 0,
                        isFinished: false
                    };
                    state.players.push(lp);
                    state.players.sort((a, b) => a.index - b.index);
                    ensureTokens();
                    snapToken(sp.index, sp.position);
                }
                lp.name = sp.name;
                lp.score = sp.score;
                lp.isFinished = sp.isFinished;
                lp.finishRank = sp.finishRank;
                if (!moving) {
                    const oldPos = lp.position;
                    lp.position = sp.position;
                    if (oldPos !== sp.position) snapToken(sp.index, sp.position);
                }
            });

            updateLobbyUI();
            renderScoreboard();
            if (!moving) renderTokens();
        }

        // ============================================================
        // Lobby
        // ============================================================
        function showLobby() {
            document.getElementById("lobby").style.display = "flex";
            document.getElementById("roomBadge").style.display = "";
            document.getElementById("roomBadge").textContent = "📋 Room: " + roomID;
            updateLobbyUI();
        }

        function hideLobby() {
            document.getElementById("lobby").style.display = "none";
        }

        function updateLobbyUI() {
            if (!isOnline) return;
            const list = document.getElementById("playerList");
            list.innerHTML = "";
            for (let i = 0; i < state.maxPlayers; i++) {
                const p = state.players.find(pl => pl.index === i);
                const row = document.createElement("div");
                row.className = "lobby-player";
                row.innerHTML = p ?
                    `<div class="dot" style="background:${PLAYER_COLORS[i]}"></div>
               <span>${escHtml(p.name)}</span>
               ${i === 0 ? '<span style="margin-left:auto;font-size:11px;opacity:0.5;">HOST</span>' : ''}` :
                    `<div class="dot" style="background:rgba(255,255,255,0.1)"></div>
               <span class="slot-empty">Slot ${i + 1} — menunggu...</span>`;
                list.appendChild(row);
            }
            const filled = state.players.length;
            document.getElementById("lobbyStatus").textContent =
                `${filled} / ${state.maxPlayers} pemain bergabung` +
                (myPlayerIndex === 0 && filled >= 2 ? " — kamu bisa mulai sekarang!" : "");
            document.getElementById("startNowBtn").style.display =
                (myPlayerIndex === 0 && filled >= 2) ? "" : "none";
        }

        // ============================================================
        // Restart
        // ============================================================
        async function restartGame() {
            document.getElementById("resultsBanner").style.display = "none";
            if (isOnline) {
                if (myPlayerIndex !== 0) {
                    alert("Hanya host yang bisa restart.");
                    return;
                }
                const res = await apiCall('restart', {
                    roomCode: roomID,
                    playerIndex: 0
                });
                if (res.success) {
                    lastKnownSeed = res.room.seed;
                    generateBoard(res.room.seed);
                    applyRoomState(res.room);
                    state.players.forEach(p => snapToken(p.index, 1));
                }
            } else {
                resetRound();
            }
        }

        function goToMenu() {
            pollingActive = false;
            location.reload();
        }

        function copyRoomLink() {
            if (!roomID) return;
            const url = window.location.origin + window.location.pathname + "?room=" + roomID;
            navigator.clipboard.writeText(url).then(() => alert("Link disalin!\n" + url));
        }

        function sleep(ms) {
            return new Promise(r => setTimeout(r, ms));
        }

        // ============================================================
        // Init
        // ============================================================
        window.onload = () => {
            const params = new URLSearchParams(window.location.search);
            const room = params.get('room');
            if (room) {
                document.getElementById("joinID").value = room;
                document.getElementById("menuStatus").textContent = "Klik Gabung untuk masuk ke room " + room;
            }
            generateBoard(42);
        };
        window.onresize = () => {
            drawLines();
            renderTokens();
        };
    </script>
</body>

</html>