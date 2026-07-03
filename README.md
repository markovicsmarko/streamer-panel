# StreamerPanel - Open Source PHP Game Server Tracker & Streamer Community Portal

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892bf.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Style](https://img.shields.io/badge/styling-Tailwind%20CSS-38bdf8.svg)](https://tailwindcss.com)

**StreamerPanel** is a lightweight, high-performance, and fully customizable PHP community website template designed specifically for gaming streamers, esports clans, and gaming communities. It acts as a central hub (Linktree-style social panel) while showcasing real-time game server statistics, embedded streams, recent YouTube uploads, a Discord leaderboard, and a secure Steam administration dashboard.

---

## 🚀 Key Features (SEO-Targeted)

- **Real-Time Game Server Status Tracker:** Powered by an optimized query system tracking player counts, current maps, and active player lists for games including:
  - *Counter-Strike 2 (CS2) & CS:GO*
  - *Minecraft (Java & Bedrock)*
  - *Call of Duty 2 (CoD2) & Call of Duty 4 (CoD4)*
  - *Modern Warfare 2 (MW2) & Modern Warfare 3 (MW3)*
  - *DayZ* and other Quake-engine / Source-engine based games.
- **Embedded Twitch Chat Bot Logger:** A background IRC-based Twitch bot script that connects to your Twitch channel chat, logs messages to a MySQL database, and keeps a real-time activity feed.
- **API Caching for Twitch & YouTube:** Automatic background caching for your latest Twitch clips and YouTube video uploads to bypass API rate limits and achieve fast load times.
- **Steam OpenID Authentication:** Secure login for administrators and users using their Steam account (no password storage required).
- **Discord Leaderboard Integration:** Showcases your Discord community's top active members using automated caching connected to the [engau.ge](https://engau.ge/) Discord bot API.
- **Bilingual & Responsive UI:** Built with a premium, responsive Tailwind CSS dark-mode design supporting automated IP-based Hungarian (HU) and English (EN) language switching.
- **Full Web-Based Installer (`install.php`):** Clean database schema creation, connection testing, and step-by-step configuration within minutes.

---

## 🛠️ System Requirements
- **PHP:** Version `>= 7.4` (Fully compatible with PHP 8.x)
- **Database:** MySQL `>= 5.7` or MariaDB
- **Extensions:** `pdo_mysql`, `curl`, `json`, `session`
- **Web Server:** Apache (with `mod_rewrite` enabled for clean `.htaccess` URLs) or Nginx

---

## 📥 Installation Guide

### 1. Upload Files
Upload all files from this repository directly to your web server's root folder (e.g., `public_html` or `www`).

### 2. Run the Interactive Installer
Open your web browser and navigate to:
```
http://your-domain.com/install.php
```

### 3. Step-by-Step Configuration
1. **Prerequisite Check:** Ensure all PHP versions and required modules are marked green.
2. **Database Settings:** Enter your MySQL connection host, database name, username, and password. You can instantly test the connection with the **Test Connection** button.
3. **API & Community Keys:**
   - **Steam API Key:** Obtain yours from [steamcommunity.com/dev/apikey](https://steamcommunity.com/dev/apikey) for Steam login.
   - **Admin Steam ID:** Enter your 17-digit SteamID64 (e.g. `76561198...`) to automatically grant yourself Superadmin privileges.
   - **Discord Guild ID & Bot Invite:** Enter your Discord Server ID. Under the field, use the provided link to invite the **engau.ge** bot to your server so the leaderboard updates automatically.
   - **Twitch & YouTube API Keys:** Paste your keys and channel names.
   - **Cron Security Key:** A custom secret key to protect your cron scripts from unauthorized public execution.
4. **Visibility Toggles:** Customize which modules (Social links, Twitch stream, YouTube player, Server list, Discord activity) should be visible on the main page.
5. **Delete Installer:** For security, **delete the `install.php` file** from your server immediately after successful setup.

---

## 🕒 Cron Jobs & Background Tasks Setup
To keep server statuses, YouTube videos, and Discord leaderboards updated, set up these tasks in your hosting control panel (e.g., cPanel Cron Jobs) to run every **5 minutes**:

### 1. Server Status & API Sync (`cron.php`)
- **CLI Command (Recommended):**
  ```bash
  /usr/local/bin/php -q /home/your_username/public_html/cron.php
  ```
- **Web Trigger Fallback:** Call the script via web-cron using:
  ```
  http://your-domain.com/cron.php?key=YOUR_CRON_SECURITY_KEY
  ```

### 2. Twitch IRC Chat Bot (`twitch_bot.php`)
The bot maintains an active IRC connection to Twitch. It is built to automatically restart every 5 minutes to bypass shared hosting process limits:
- **Cron Job Command:**
  ```bash
  /usr/local/bin/php -q /home/your_username/public_html/twitch_bot.php
  ```

---

## 🇭🇺 Magyar nyelvű leírás (Hungarian Summary)

A **StreamerPanel** egy nyílt forráskódú, modern PHP alapú játékszerver-követő és közösségi portál streamereknek és klánoknak. 

### Főbb jellemzők:
- **Valós idejű szerver státusz kijelzés:** Játékoslisták, térképek és ping lekérések (CS2, Minecraft, DayZ, CoD2, CoD4, MW2, MW3).
- **Twitch IRC Chat Bot:** Automatikus üzenetnaplózás adatbázisba a Twitch csatornádról.
- **Közösségi funkciók:** YouTube videó és Twitch klipek gyorsítótárazása, beágyazott Twitch lejátszó, Linktree-stílusú linkgyűjtemény.
- **Discord ranglista:** Engau.ge Discord bot alapú aktivitási toplista integráció.
- **Steam Login:** Biztonságos, jelszómentes bejelentkezés adminisztrátoroknak.
- **Webes Telepítő (`install.php`):** Egyszerű, 5 perces interaktív telepítőfelület adatbázis-teszteléssel és beállításokkal.

*Telepítés után a biztonság érdekében mindenképpen töröld az `install.php` fájlt a szerverről!*

---

## 🔒 Security & License
This project is open-source and licensed under the **MIT License**. Remember to restrict write permissions on `config.php` and keep your API keys private.
