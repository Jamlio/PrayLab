# 🙏 PrayLab

**PrayLab** is a lightweight, modern spiritual toolkit designed to help users track, manage, and reflect on their daily prayers and habits. Built for simplicity, speed, and elegance, this script is optimized for display monitors or TVs and can be controlled from any device. It’s perfect for hanging in a mosque, prayer room, or community space—providing an easy way to view prayer times, live soccer data, livestreams, and community announcements.

---

## 🖥️ What Is It?

PrayLab is designed to run on a display monitor or TV—making it ideal for:

- 🕌 Mosques and prayer halls
- 🏠 Personal prayer corners
- 🏢 Community centers
- 📺 Any space where a visual dashboard is helpful

It can be controlled from any device (phone, tablet, or computer) and displays:

- 🕋 Daily prayer times
- ⚽ Live soccer scores
- 📡 Livestreams (e.g., YouTube, Twitch)
- 📢 Community announcements

## 📌 Overview

PrayLab includes:

- A clean, responsive web interface
- Prayer tracking and journaling features
- Configurable settings via `config.json` or the control panel. (Accesebull from any device)
- A smart installer (`praylab.php`) that handles setup automatically without the need to install a zip package

---

## ⚙️ Requirements

To run PrayLab, your server must support:

- PHP **7.4+**
- PHP extensions:
  - `zip`
  - `curl`
  - `json`
- Write permissions in the root directory
- Internet access (for installer and data updates)

---

## ✨ Features

### 🧠 Core App Features

- 📆 **Prayer Tracker**: Log and view daily prayers (updated in real time)
- 📝 **Journal Entries**: Reflect and record spiritual notes
- 🌙 **Dark Mode**: Beautiful, distraction-free interface
- 🔧 **Configurable Settings**: Customize via `config.json` and control panel
- 🌐 **Offline-Friendly**: Minimal dependencies, fast loading
- 🔒 **Privacy-First**: No external analytics or tracking, the script is yours

### 🛠️ Installer Features (`praylab.php`)

- 📡 Fetches latest version from PrayLab server
- 🎨 Dark-themed UI with animated branding
- 📊 Live status updates during install
- 🧹 Self-deletes after successful setup
- 🔁 Redirects to `index.php` automatically
- 🧾 Logs progress to `install.log`

---

## ⚙️ Installation Guide

PrayLab can be installed in two easy ways: using a single-file installer or by uploading the full ZIP package.


### 🧩 Option 1: Single-File Installer

This is the fastest way to get PrayLab up and running.

#### ✅ Steps:

1. **Download the installer**  
   Get the latest `praylab.php` file from the [Releases](https://pray-lab.vercel.app/praylab.php).

2. **Upload to your server**  
   Place `praylab.php` in the root directory of your PHP-enabled server.

3. **Run the installer**  
   Open your browser and navigate to: http://yourdomain.com/praylab.php

4. **Follow the setup prompts**  
The installer will automatically download the latest version, extract files, and configure your environment.


### 📦 Option 2: Full ZIP Package

Use this method if you prefer manual setup or want to customize files before installation.

#### ✅ Steps:

1. **Download the ZIP**  
Get the latest release from the [Releases](https://pray-lab.vercel.app/latest-version.json).

2. **Extract the ZIP**  
Unzip the package to your server.

3. **Upload to your server**  
Use FTP, SCP, or your hosting control panel to upload all files to your server directory.

4. **Open in browser**  
Navigate to: http://yourdomain.com/

5. **Configure settings**  
Follow the configuration proccess and you should be good to go.

---

## 🖥️ Display Setup

- Connect a monitor or TV to your server or device. (We recommend Kiosk mode browser on Android tv's, or use a Tv stick)
- Open PrayLab in full-screen.
- Control settings remotely from any device.

---

Made with ❤️ by Legendukas
