# 🌟 Symfony Sponsorship

[![Symfony 7.2](https://img.shields.io/badge/Symfony-7.2-black.svg?style=for-the-badge&logo=symfony)](https://symfony.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?style=for-the-badge&logo=php)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC.svg?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![UX Turbo](https://img.shields.io/badge/Symfony%20UX-Turbo%20%2B%20Live-673AB7.svg?style=for-the-badge)](https://ux.symfony.com)
[![Brevo CRM](https://img.shields.io/badge/Brevo-CRM%20Sync-0B996F.svg?style=for-the-badge)](https://brevo.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)

> The plug-and-play viral referral & sponsorship engine for Symfony 7.2. Launch high-converting viral loops, gamified milestone tiers, live leaderboards, and magic link authentication in minutes.

---

## ✨ Features

### 🚀 Automated Viral Attribution Loop
- **Smart Link Interceptor (`?ref=CODE`)**: Captures referral links with a 30-day secure HTTP-Only cookie & session tracking ([`ReferralTrackingListener.php`](src/EventListener/ReferralTrackingListener.php)).
- **Automatic Attribution**: Automatically links new signups to their referrer upon registration ([`ReferralService.php`](src/Service/ReferralService.php)).
- **Anti-Fraud Engine**: Prevents self-referral, same-IP spamming, and temporary emails.

### 🏆 Gamification & Milestone Tiers
- **4 Progressive Reward Tiers** ([`RewardTier.php`](src/Enum/RewardTier.php)):
  - 🥉 **Bronze Supporter** (1 referral): 50 bonus points & early access.
  - 🥈 **Silver Champion** (3 referrals): 200 bonus points & 10% discount promo code.
  - 🥇 **Gold Ambassador** (5 referrals): 500 bonus points & 1 month free PRO access.
  - 👑 **Platinum VIP** (10+ referrals): 1,500 bonus points & lifetime VIP perks.

### ⚡ Symfony UX LiveComponents (Zero JS Build Fatigue)
- **`<twig:ReferralShareCard />`**:
  - Unique personal invite link with 1-click clipboard copy.
  - 1-click instant social share buttons (*WhatsApp, X / Twitter, LinkedIn, Telegram, Email*).
  - Real-time stat counters (*Clicks, Friends Joined, Points Earned*).
- **`<twig:MilestoneTracker />`**:
  - Visual gamified progress bar towards the next reward unlock.
- **`<twig:ReferralLeaderboard />`**:
  - Real-time competitive leaderboard with medals (🥇, 🥈, 🥉) and personal rank badge.
- **`<twig:AssignmentEmail />`**:
  - Batch personalized invitation emails with embedded referral URLs.

### 🔒 Passwordless Magic Link & CRM
- Zero passwords: instantaneous login link delivery via Symfony Mailer / Mailpit.
- Automated 2-way contact & custom attribute synchronization with **Brevo CRM**.

---

## 🛠️ Tech Stack

- **Framework**: PHP 8.2+, Symfony 7.2
- **Front-end**: Tailwind CSS, DaisyUI, Webpack Encore, Hotwired Stimulus, Symfony UX LiveComponents
- **Database**: Doctrine ORM 3.x, MySQL 8.0, Redis
- **Integrations**: Brevo API SDK (`getbrevo/brevo-php`)

---

## 🚀 Quick Start

### 1. Clone the repository
```bash
git clone https://github.com/charlesen/symfony-sponsorship.git
cd symfony-sponsorship
```

### 2. Start with Docker
```bash
chmod +x docker/scripts/dev.sh
./docker/scripts/dev.sh
```

### 3. Access Services
- 🌐 **Application**: [http://localhost:8080](http://localhost:8080)
- 📧 **Mailpit**: [http://localhost:8025](http://localhost:8025)
- 🗄️ **Adminer**: [http://localhost:8081](http://localhost:8081)

---

## 🔑 Demo Test Accounts

| Account | Email | Referrer Code | Referrals | Points | Tier |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Alice Dupont** | `alice@example.com` | `ALICE100` | 12 | 1,850 pts | 👑 **Platinum VIP** |
| **Bob Martin** | `bob@example.com` | `BOB20000` | 6 | 850 pts | 🥇 **Gold Ambassador** |
| **Chloé Bernard** | `chloe@example.com` | `CHLOE300` | 3 | 350 pts | 🥈 **Silver Champion** |
| **David Petit** | `david@example.com` | `DAVID400` | 1 | 70 pts | 🥉 **Bronze Supporter** |
| **John Doe** | `user@example.com` | `JOHNDOE1` | 0 | 20 pts | Member |
| **Admin** | `admin@example.com` | `ADMIN001` | - | 500 pts | **ROLE_ADMIN** |

---

## 📄 License

Distributed under the **MIT License**. See [LICENSE](LICENSE) for details.
