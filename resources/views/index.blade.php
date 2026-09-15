<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BDE — Bureau des Étudiants</title>
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<header>
  <nav class="wrap">
    <div class="logo-mark">
      <div class="logo-badge">BDE</div>
      <span>BDE&nbsp;Campus</span>
    </div>
    <ul class="nav-links">
      <li><a href="#evenements">Événements</a></li>
      <li><a href="#classement">Classement</a></li>
      <li><a href="#pourquoi">Le BDE</a></li>
      <li><a href="#footer">Contact</a></li>
    </ul>
    <a href="#" class="nav-cta">Adhérer</a>
    <button class="burger" aria-label="Menu"><span></span><span></span><span></span></button>
  </nav>
</header>

<section class="hero">
  <div class="wrap hero-inner">
    <div>
      <div class="eyebrow-pill"><span class="dot"></span>Inscriptions ouvertes pour la rentrée</div>
      <h1>La vie étudiante,<br>ça se <span class="accent">gagne des points</span>.</h1>
      <p>Événements, soirées, sport, entraide : participe à la vie du campus, grimpe au classement et débloque des récompenses tout au long de l'année.</p>
      <div class="hero-actions">
        <a href="#" class="btn-primary">Devenir adhérent</a>
        <a href="#evenements" class="btn-ghost">Voir les événements</a>
      </div>
      <div class="hero-stats">
        <div><strong>1 240</strong><span>étudiants adhérents</span></div>
        <div><strong>36</strong><span>événements / an</span></div>
        <div><strong>18</strong><span>pôles &amp; clubs</span></div>
      </div>
    </div>

    <div class="event-spotlight">
      <span class="tag">Prochain événement</span>
      <div class="event-spotlight-img"></div>
      <h3>Soirée d'intégration — Rentrée 2026</h3>
      <p class="meta">Vendredi 19 septembre · 20h · Le Hangar &nbsp;·&nbsp; <strong>+5 pts classement</strong></p>
      <div class="row">
        <span class="price">8€ adhérents</span>
        <a href="#" class="join">Je m'inscris</a>
      </div>
    </div>
  </div>
</section>

<section id="evenements">
  <div class="wrap">
    <div class="section-head">
      <div class="kicker">Agenda</div>
      <h2>Les prochains événements</h2>
      <p>Chaque participation compte pour ton classement annuel. Inscris-toi en quelques clics.</p>
    </div>
    <div class="events-strip">
      <div class="event-card soiree">
        <div class="band"></div>
        <div class="body">
          <div class="date-row">📅 19 septembre — 20h</div>
          <h3>Soirée d'intégration</h3>
          <p>Le Hangar — ouverte à tous les nouveaux adhérents.</p>
          <div class="foot">
            <span class="points-chip">+5 pts</span>
            <span class="arrow">→</span>
          </div>
        </div>
      </div>
      <div class="event-card sport">
        <div class="band"></div>
        <div class="body">
          <div class="date-row">📅 27 septembre — 14h</div>
          <h3>Tournoi inter-promos</h3>
          <p>Foot, basket, volley — au gymnase du campus.</p>
          <div class="foot">
            <span class="points-chip">+10 pts</span>
            <span class="arrow">→</span>
          </div>
        </div>
      </div>
      <div class="event-card solidaire">
        <div class="band"></div>
        <div class="body">
          <div class="date-row">📅 4 octobre — 10h</div>
          <h3>Collecte solidaire</h3>
          <p>Maraude et collecte de dons avec les Restos du Cœur.</p>
          <div class="foot">
            <span class="points-chip">+15 pts</span>
            <span class="arrow">→</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="classement" class="classement">
  <div class="wrap">
    <div class="section-head">
      <div class="kicker" style="color:var(--orange-clair)">Gamification</div>
      <h2>Le classement des étudiants</h2>
      <p>Plus tu participes, plus tu grimpes. Consulte ton rang et débloque des badges.</p>
    </div>

    <div class="podium-wrap">
      <div class="podium-step p2">
        <div class="avatar">LM</div>
        <div class="name">Léa M.</div>
        <div class="pts">312 pts</div>
        <div class="bar">2</div>
      </div>
      <div class="podium-step p1">
        <div class="avatar">TR</div>
        <div class="name">Tom R.</div>
        <div class="pts">348 pts</div>
        <div class="bar">1</div>
      </div>
      <div class="podium-step p3">
        <div class="avatar">SK</div>
        <div class="name">Sofia K.</div>
        <div class="pts">289 pts</div>
        <div class="bar">3</div>
      </div>
    </div>

    <div class="rank-table">
      <div class="rank-row"><span class="num">4</span><span class="name">Nathan B.</span><span class="pts">265 pts</span></div>
      <div class="rank-row"><span class="num">5</span><span class="name">Inès D.</span><span class="pts">241 pts</span></div>
      <div class="rank-row"><span class="num">6</span><span class="name">Hugo P.</span><span class="pts">228 pts</span></div>
      <div class="rank-row"><span class="num">7</span><span class="name">Chloé V.</span><span class="pts">210 pts</span></div>
    </div>
  </div>
</section>

<section id="pourquoi">
  <div class="wrap">
    <div class="section-head">
      <div class="kicker">Pourquoi le BDE</div>
      <h2>Tout ton campus, en un seul endroit</h2>
      <p>Le site centralise ce qui fait la vie étudiante — inscriptions, communauté et récompenses.</p>
    </div>
    <div class="features-grid">
      <div class="feature">
        <div class="icon">🎟️</div>
        <h3>Inscriptions en ligne</h3>
        <p>Réserve ta place aux événements et paie en quelques secondes.</p>
      </div>
      <div class="feature">
        <div class="icon">🏆</div>
        <h3>Points &amp; badges</h3>
        <p>Chaque événement rapporte des points et débloque des récompenses.</p>
      </div>
      <div class="feature">
        <div class="icon">🤝</div>
        <h3>Petites annonces</h3>
        <p>Covoiturage, logement, matériel : entraide entre étudiants.</p>
      </div>
      <div class="feature">
        <div class="icon">👥</div>
        <h3>Annuaire des pôles</h3>
        <p>Retrouve les 18 clubs et associations du campus.</p>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="cta-band">
      <h2>Prêt à rejoindre la communauté ?</h2>
      <a href="#" class="btn-primary">Adhérer au BDE — 15€/an</a>
    </div>
  </div>
</section>

<footer id="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="logo-mark"><div class="logo-badge">BDE</div><span style="color:#fff">BDE Campus</span></div>
        <p>Le site officiel du Bureau des Étudiants — événements, classement et vie associative.</p>
      </div>
      <div>
        <h4>Navigation</h4>
        <ul>
          <li><a href="#evenements">Événements</a></li>
          <li><a href="#classement">Classement</a></li>
          <li><a href="#pourquoi">Le BDE</a></li>
        </ul>
      </div>
      <div>
        <h4>Ressources</h4>
        <ul>
          <li><a href="#">Boutique</a></li>
          <li><a href="#">Annuaire des pôles</a></li>
          <li><a href="#">Galerie photos</a></li>
        </ul>
      </div>
      <div>
        <h4>Contact</h4>
        <ul>
          <li>contact@bde-campus.fr</li>
          <li>06 12 34 56 78</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 BDE Campus — Tous droits réservés</span>
      <div class="socials">
        <a href="#">IG</a>
        <a href="#">FB</a>
        <a href="#">TT</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>
