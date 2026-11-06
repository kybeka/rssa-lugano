<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- ─── Primary SEO ──────────────────────────────────────── -->
  <title>RSSA Lugano — Russian Speaking Student Association at USI</title>
  <meta name="description" content="The Russian Speaking Student Association at Università della Svizzera italiana in Lugano. A welcoming, non-profit community open to all USI students. Events, culture, and connection.">
  <link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">

  <!-- ─── Open Graph (social sharing) ─────────────────────── -->
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?php echo esc_url(home_url('/')); ?>">
  <meta property="og:site_name"   content="RSSA Lugano">
  <meta property="og:title"       content="RSSA Lugano — Russian Speaking Student Association at USI">
  <meta property="og:description" content="A welcoming, non-profit community for Russian-speaking students at USI Lugano. Open to all. Events every semester.">
  <meta property="og:locale"      content="en_US">

  <!-- ─── Twitter / X Card ────────────────────────────────── -->
  <meta name="twitter:card"        content="summary">
  <meta name="twitter:title"       content="RSSA Lugano — Russian Speaking Student Association at USI">
  <meta name="twitter:description" content="A welcoming, non-profit community for Russian-speaking students at USI Lugano.">
  <!-- ─── Identity & theme ────────────────────────────────── -->
  <meta name="author"      content="RSSA Lugano">
  <meta name="theme-color" content="#a03020">
  <link rel="icon" href="<?php echo esc_url(get_theme_file_uri("icon.svg")); ?>" type="image/svg+xml">
  <!-- ─── Fonts ───────────────────────────────────────────── -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,300;12..96,400;12..96,500;12..96,600;12..96,700&family=Spectral:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo esc_url(add_query_arg('ver', substr(hash_file('sha256', get_stylesheet_directory() . '/style.css'), 0, 12), get_stylesheet_uri())); ?>">
  <?php if (!function_exists('rssat_enabled') || !rssat_enabled()) : ?><link rel="stylesheet" href="https://assets.juicer.io/embed.css" media="all"><?php endif; ?>

  <!-- ─── Structured data ─────────────────────────────────── -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "https://rssa.usi.ch/#organization",
        "name": "Russian Speaking Student Association",
        "alternateName": "RSSA Lugano",
        "url": "https://rssa.usi.ch/",
        "description": "A welcoming, non-political, non-profit student association at the Università della Svizzera italiana in Lugano, Switzerland. Open to all USI students.",
        "foundingDate": "2025",
        "email": "RSSA@usi.ch",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "Via Giuseppe Buffi 13",
          "addressLocality": "Lugano",
          "postalCode": "6900",
          "addressCountry": "CH"
        },
        "parentOrganization": {
          "@type": "CollegeOrUniversity",
          "name": "Università della Svizzera italiana",
          "url": "https://www.usi.ch",
          "sameAs": "https://www.wikidata.org/wiki/Q594223"
        },
        "sameAs": [
          "https://t.me/RSSA_Lugano",
          "https://www.instagram.com/rssa_lugano/"
        ]
      },
      {
        "@type": "WebSite",
        "@id": "https://rssa.usi.ch/#website",
        "url": "https://rssa.usi.ch/",
        "name": "RSSA Lugano",
        "publisher": { "@id": "https://rssa.usi.ch/#organization" }
      }
    ]
  }
  </script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>

  <a class="skip-link" href="#main-content">Skip to content</a>

  <!-- ─── NAV ─────────────────────────────────────────────────── -->
  <header class="nav" id="nav" role="banner">
    <div class="container">
      <div class="nav__bar">
        <a href="#" class="nav__logo" aria-label="RSSA Lugano — Return to top">
          <img class="nav__logo-img" src="<?php echo esc_url(rssa_image('image_1')); ?>" alt="<?php echo esc_attr(rssa_image_alt('image_1')); ?>" width="1254" height="1254">
        </a>
        <nav class="nav__links" aria-label="Primary">
          <a class="nav__link" href="#about">About</a>
          <a class="nav__link" href="#latest">Latest updates</a>          <?php if (rssa_gallery()) : ?><a class="nav__link" href="#gallery">Photos</a><?php endif; ?>

          <a class="nav__link" href="#team">Team</a>
          <a class="nav__link" href="#connect">Contact</a>
          <a class="nav__join" href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener noreferrer">Join Us</a>
        </nav>
        <button class="nav__toggle" id="navToggle" aria-label="Open navigation" aria-expanded="false" aria-controls="navDrawer">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
    <nav class="nav__drawer" id="navDrawer" aria-label="Mobile navigation">
      <a class="nav__drawer-link" href="#about">About</a>
      <a class="nav__drawer-link" href="#latest">Latest updates</a>      <?php if (rssa_gallery()) : ?><a class="nav__drawer-link" href="#gallery">Photos</a><?php endif; ?>

      <a class="nav__drawer-link" href="#team">Team</a>
          <a class="nav__drawer-link" href="#connect">Contact</a>
      <a class="nav__drawer-link" href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener noreferrer">Join Us →</a>
    </nav>
  </header>

  <!-- ─── HERO ─────────────────────────────────────────────────── -->
  <main id="main-content">
    <section class="hero" aria-labelledby="hero-hed">
      <div class="container hero__main">
        <p class="hero__eyebrow reveal"><?php echo rssa_text('text_1'); ?></p>
        <h1 class="hero__headline reveal" data-delay="1" id="hero-hed"><?php echo rssa_text('text_2'); ?></h1>
      </div>
      <div class="hero__footer">
        <div class="container">
          <div class="hero__footer-inner">
            <p class="hero__desc reveal" data-delay="2"><?php echo rssa_text('text_3'); ?></p>
            <div class="hero__cta-group reveal" data-delay="3">
              <p class="hero__location"><?php echo rssa_text('text_4'); ?></p>
              <a class="btn btn-dark" href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener noreferrer">
                Join on Telegram
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M7 2l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── ABOUT ─────────────────────────────────────────────── -->
    <section class="about section-pad" id="about" aria-labelledby="about-hed">
      <div class="container">
        <header class="sec-hd reveal">
          <span class="sec-num">01</span>
          <h2 class="sec-title" id="about-hed"><?php echo rssa_text('text_5'); ?></h2>
        </header>
        <div class="about__layout">
          <div class="about__sidebar reveal" data-delay="1">
            <blockquote class="about__pull">
              <p class="about__pull-text"><?php echo rssa_text('founder_quote'); ?></p>
              <footer class="about__pull-attr">
                <strong>Katherine Lerner-Teves</strong><br>
                President &amp; Founder
              </footer>
            </blockquote>
            <div class="about__stats" role="list" aria-label="Key facts">
              <div class="about__stat" role="listitem">
                <div class="about__stat-val">2025</div>
                <div class="about__stat-lbl">Founded</div>
              </div>
              <div class="about__stat" role="listitem">
                <div class="about__stat-val">All</div>
                <div class="about__stat-lbl">Are welcome</div>
              </div>
            </div>
          </div>
          <div class="about__prose">
            <p class="about__lead reveal" data-delay="1"><?php echo rssa_text('text_6'); ?></p>
            <p class="about__body reveal" data-delay="2"><?php echo rssa_text('text_7'); ?></p>
          </div>
          <div class="about__photo-wrap reveal" data-delay="2">
            <img class="about__photo" src="<?php echo esc_url(rssa_image('image_2')); ?>" alt="<?php echo esc_attr(rssa_image_alt('image_2')); ?>" width="1042" height="1474" loading="lazy">
          </div>
        </div>
        <div class="about__pillars reveal" data-delay="3" role="list" aria-label="Our values">
          <div class="about__pillar" role="listitem">
            <p class="about__pillar-title"><?php echo rssa_text('text_8'); ?></p>
            <p class="about__pillar-body"><?php echo rssa_text('text_9'); ?></p>
          </div>
          <div class="about__pillar" role="listitem">
            <p class="about__pillar-title"><?php echo rssa_text('text_10'); ?></p>
            <p class="about__pillar-body"><?php echo rssa_text('text_11'); ?></p>
          </div>
          <div class="about__pillar" role="listitem">
            <p class="about__pillar-title"><?php echo rssa_text('text_12'); ?></p>
            <p class="about__pillar-body"><?php echo rssa_text('text_13'); ?></p>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── MANIFESTO ─────────────────────────────────────────── -->
    <section class="manifesto" aria-label="Our manifesto">
      <div class="container">
        <div class="manifesto__layout">
          <blockquote class="manifesto__quote reveal"><?php echo rssa_text('text_14'); ?></blockquote>
          <div class="manifesto__aside reveal" data-delay="1">
            <p class="manifesto__aside-text"><?php echo rssa_text('text_15'); ?></p>
            <a class="btn btn-ghost-light" href="#connect">Get in touch</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── LATEST ───────────────────────────────────────────── -->
    <section class="latest section-pad" id="latest" aria-labelledby="latest-hed">
      <div class="container">
        <header class="sec-hd reveal">
          <span class="sec-num">02</span>
          <h2 class="sec-title" id="latest-hed"><?php echo rssa_text('text_16'); ?></h2>
          <a class="sec-more" href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener noreferrer">Telegram ↗</a>
        </header>

        <?php if (function_exists('rssat_enabled') && rssat_enabled()) { rssat_render(); } else { ?>
        <!--
          ── JUICER SETUP ────────────────────────────────────────────
          1. Create a free account at juicer.io
          2. Add source: Instagram  → connect @rssa_lugano
          3. Add source: Telegram   → connect @RSSA_Lugano
          4. Your feed name appears in the dashboard URL:
             juicer.io/feeds/YOUR-FEED-ID/edit
          5. Replace YOUR-FEED-ID below with that name, then commit.
          ────────────────────────────────────────────────────────────
        -->
        <div class="juicer-feed" data-feed-id="https-rss-app-feeds-5oi3yrzxmcejmyvi-xml" data-per="6" data-truncate="120" data-overlay="false"></div>

        <p class="latest__fallback" id="latestFallback">
          Follow us on
          <a href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener noreferrer">Telegram</a>
          for the latest updates and upcoming events.
        </p>
        <?php } ?>
      </div>
    </section>

    <?php rssa_render_gallery(); ?>

    <!-- ─── TEAM ──────────────────────────────────────────────── -->
    <section class="team section-pad" id="team" aria-labelledby="team-hed">
      <div class="container">
        <header class="sec-hd reveal">
          <span class="sec-num"><?php echo rssa_gallery() ? '04' : '03'; ?></span>
          <h2 class="sec-title" id="team-hed"><?php echo rssa_text('text_17'); ?></h2>
        </header>
        <div class="team__grid">
          <article class="team-card reveal">
            <div class="team-card__photo">
              <img src="<?php echo esc_url(rssa_image('image_3')); ?>"
                     alt="<?php echo esc_attr(rssa_image_alt('image_3')); ?>"
                     width="806" height="668"
                     loading="lazy">
            </div>
            <div class="team-card__body">
              <p class="team-card__role"><?php echo rssa_text('text_18'); ?></p>
              <h3 class="team-card__name"><?php echo rssa_text('text_19'); ?></h3>
              <p class="team-card__bio"><?php echo rssa_text('text_20'); ?></p>
              <div class="team-card__socials">
                <a class="team-card__social" href="https://linkedin.com/in/katherine-lerner-teves-3301b2237" target="_blank" rel="noopener noreferrer" aria-label="Katherine on LinkedIn">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                  LinkedIn
                </a>
                <a class="team-card__social" href="https://www.facebook.com/katherine.lernerteves" target="_blank" rel="noopener noreferrer" aria-label="Katherine on Facebook">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                  Facebook
                </a>
              </div>
            </div>
          </article>
          <article class="team-card reveal" data-delay="1">
            <div class="team-card__photo">
              <img src="<?php echo esc_url(rssa_image('image_4')); ?>"
                     alt="<?php echo esc_attr(rssa_image_alt('image_4')); ?>"
                     width="636" height="527"
                     loading="lazy">
            </div>
            <div class="team-card__body">
              <p class="team-card__role"><?php echo rssa_text('text_21'); ?></p>
              <h3 class="team-card__name"><?php echo rssa_text('text_22'); ?></h3>
              <p class="team-card__bio"><?php echo rssa_text('text_23'); ?></p>
              <div class="team-card__socials">
                <a class="team-card__social" href="https://linkedin.com/in/david-bocherov-41a92b351" target="_blank" rel="noopener noreferrer" aria-label="David on LinkedIn">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                  LinkedIn
                </a>
              </div>
            </div>
          </article>
          <article class="team-card reveal" data-delay="2">
            <div class="team-card__photo">
              <img src="<?php echo esc_url(rssa_image('image_5')); ?>"
                     alt="<?php echo esc_attr(rssa_image_alt('image_5')); ?>"
                     width="806" height="668"
                     loading="lazy">
            </div>
            <div class="team-card__body">
              <p class="team-card__role"><?php echo rssa_text('text_24'); ?></p>
              <h3 class="team-card__name"><?php echo rssa_text('text_25'); ?></h3>
              <p class="team-card__bio"><?php echo rssa_text('text_26'); ?></p>
              <div class="team-card__socials">
                <a class="team-card__social" href="https://linkedin.com/in/aleksandr-volkov-9008b115b" target="_blank" rel="noopener noreferrer" aria-label="Aleksandr on LinkedIn">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                  LinkedIn
                </a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- ─── CONNECT ───────────────────────────────────────────── -->

    <section class="connect section-pad" id="connect" aria-labelledby="connect-hed">
      <div class="container">
        <header class="sec-hd reveal">
          <span class="sec-num"><?php echo rssa_gallery() ? '05' : '04'; ?></span>
          <h2 class="sec-title" id="connect-hed"><?php echo rssa_text('text_27'); ?></h2>
        </header>
        <div class="connect__layout">
          <div>
            <p class="connect__hed reveal"><?php echo rssa_text('text_28'); ?></p>
            <p class="connect__body reveal" data-delay="1"><?php echo rssa_text('text_29'); ?></p>
            <div class="connect__channels reveal" data-delay="2">
              <a class="channel" href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener noreferrer" aria-label="Join our Telegram channel, @RSSA_Lugano">
                <span class="channel__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L6.148 14.53l-2.946-.924c-.64-.203-.654-.64.136-.948l11.498-4.43c.537-.194 1.006.131.726.02z"/></svg>
                </span>
                <div class="channel__info">
                  <p class="channel__name"><?php echo rssa_text('text_30'); ?></p>
                  <p class="channel__handle"><?php echo rssa_text('text_31'); ?></p>
                </div>
                <span class="channel__arr" aria-hidden="true">→</span>
              </a>
              <a class="channel" href="https://instagram.com/rssa_lugano" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Instagram, @rssa_lugano">
                <span class="channel__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </span>
                <div class="channel__info">
                  <p class="channel__name"><?php echo rssa_text('text_32'); ?></p>
                  <p class="channel__handle"><?php echo rssa_text('text_33'); ?></p>
                </div>
                <span class="channel__arr" aria-hidden="true">→</span>
              </a>
              <a class="channel" href="mailto:RSSA@usi.ch" aria-label="Email us at RSSA@usi.ch">
                <span class="channel__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </span>
                <div class="channel__info">
                  <p class="channel__name"><?php echo rssa_text('text_34'); ?></p>
                  <p class="channel__handle"><?php echo rssa_text('text_35'); ?></p>
                </div>
                <span class="channel__arr" aria-hidden="true">→</span>
              </a>
            </div>
            <div class="connect__shoutout reveal" data-delay="3">
              <p class="connect__shoutout-label"><?php echo rssa_text('text_36'); ?></p>
              <a class="channel channel--aside" href="https://chat.whatsapp.com/FQS7h8Q3d3uF8TX6wSkyKM" target="_blank" rel="noopener noreferrer" aria-label="Join Russian and Ukrainian language group on WhatsApp">
                <span class="channel__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </span>
                <div class="channel__info">
                  <p class="channel__name"><?php echo rssa_text('text_37'); ?></p>
                  <p class="channel__handle"><?php echo rssa_text('text_38'); ?></p>
                </div>
                <span class="channel__arr" aria-hidden="true">→</span>
              </a>
            </div>
          </div>

          <div class="reveal" data-delay="2">
            <form
              class="form"
              id="contactForm"
              method="post"
              action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
              novalidate
              aria-label="Contact form"
            >
              <input type="hidden" name="action" value="rssa_contact">
              <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('rssa_contact')); ?>">
              <div class="contact-trap" aria-hidden="true"><label>Leave this field empty<input name="website" type="text" tabindex="-1" autocomplete="off"></label></div>
              <noscript>Please email <a href="mailto:rssa@usi.ch">rssa@usi.ch</a> if JavaScript is unavailable.</noscript>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="firstName">First name</label>
                  <input class="form-field" type="text" id="firstName" name="firstName" autocomplete="given-name" required placeholder="Your first name">
                </div>
                <div class="form-group">
                  <label class="form-label" for="lastName">Last name</label>
                  <input class="form-field" type="text" id="lastName" name="lastName" autocomplete="family-name" required placeholder="Your last name">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input class="form-field" type="email" id="email" name="email" autocomplete="email" required placeholder="you@usi.ch">
              </div>
              <div class="form-group">
                <label class="form-label" for="message">Message</label>
                <textarea class="form-field" id="message" name="message" required placeholder="Tell us about yourself, or ask a question…"></textarea>
              </div>
              <div>
                <p class="form-label">Your name, email and message will be emailed to RSSA so we can reply.</p>
                <button class="btn btn-dark" type="submit" id="formSubmit">Send message</button>
                <p class="form-status" id="formStatus" aria-live="polite" role="status"></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- ─── FOOTER ────────────────────────────────────────────────── -->
  <footer class="footer" role="contentinfo">
    <div class="container">
      <div class="footer__grid">
        <div class="footer__brand">
          <p class="footer__logo"><?php echo rssa_text('text_39'); ?></p>
          <p class="footer__tagline"><?php echo rssa_text('text_40'); ?></p>
          <p class="footer__disclaimer"><?php echo rssa_text('text_41'); ?></p>
        </div>
        <div>
          <p class="footer__col-label"><?php echo rssa_text('text_42'); ?></p>
          <nav class="footer__nav" aria-label="Footer navigation">
            <a class="footer__nav-link" href="#about">About</a>
            <a class="footer__nav-link" href="#latest">Latest updates</a>            <?php if (rssa_gallery()) : ?><a class="footer__nav-link" href="#gallery">Photos</a><?php endif; ?>

            <a class="footer__nav-link" href="#team">Team</a>
          <a class="footer__nav-link" href="#connect">Contact</a>
          </nav>
        </div>
        <div>
          <p class="footer__col-label"><?php echo rssa_text('text_43'); ?></p>
          <nav class="footer__nav" aria-label="Social links">
            <a class="footer__nav-link" href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener noreferrer">Telegram</a>
            <a class="footer__nav-link" href="https://instagram.com/rssa_lugano" target="_blank" rel="noopener noreferrer">Instagram</a>
            <a class="footer__nav-link" href="mailto:RSSA@usi.ch">Email</a>
          </nav>
        </div>
      </div>
      <div class="footer__base">
        <p class="footer__copy"><?php echo rssa_text('text_44'); ?> <span class="footer__credit">built with &lt;3 by <a href="https://github.com/kybeka" target="_blank" rel="noopener noreferrer" aria-label="Kyla on GitHub">kyla</a></span></p>
        <address class="footer__address">
          Via Giuseppe Buffi 13, 6900 Lugano, Switzerland
        </address>
      </div>
    </div>
  </footer>

  <script src="<?php echo esc_url(add_query_arg('ver', substr(hash_file('sha256', get_theme_file_path('script.js')), 0, 12), get_theme_file_uri('script.js'))); ?>"></script>
  <?php if (!function_exists('rssat_enabled') || !rssat_enabled()) : ?><script src="https://www.juicer.io/embed/https-rss-app-feeds-5oi3yrzxmcejmyvi-xml/embed-code.js" async defer></script><?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
