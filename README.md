# 🙏 PrayLab

**PrayLab** is a lightweight, modern spiritual toolkit designed to help users track, manage, and reflect on their daily prayers and habits. Built for simplicity, speed, and elegance, this script is optimized for display monitors or TVs and can be controlled from any device. It’s perfect for hanging in a mosque, prayer room, or community space—providing an easy way to view prayer times, live soccer data, livestreams, and community announcements.

---

## 📌 Overview

PrayLab includes:

- A clean, responsive web interface
- Prayer tracking and journaling features
- Configurable settings via `config.json`
- A smart installer (`praylab.php`) that handles setup automatically

---

## ⚙️ Requirements

To run PrayLab, your server must support:

- PHP **7.4+**
- PHP extensions:
  - `zip`
  - `curl`
  - `json`
- Write permissions in the root directory
- Internet access (for installer and updates)

---

## ✨ Features

### 🧠 Core App Features

- 📆 **Prayer Tracker**: Log and view daily prayers
- 📝 **Journal Entries**: Reflect and record spiritual notes
- 🌙 **Dark Mode**: Beautiful, distraction-free interface
- 🔧 **Configurable Settings**: Customize via `config.json`
- 🌐 **Offline-Friendly**: Minimal dependencies, fast loading
- 🔒 **Privacy-First**: No external analytics or tracking

### 🛠️ Installer Features (`praylab.php`)

- 📡 Fetches latest version from PrayLab server
- 🎨 Dark-themed UI with animated branding
- 📊 Live status updates during install
- 🧹 Self-deletes after successful setup
- 🔁 Redirects to `index.php` automatically
- 🧾 Logs progress to `install.log`

---

## 📁 File Structure

