<?php
/*
Template Name: Login Page
*/
?>
<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta content="IE=100" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="noindex,nofollow,noarchive" name="robots">

    <title>Log In &middot; myUMBC</title>
    <meta name="description" content="Log in to myUMBC, the campus portal for the University of Maryland, Baltimore County.">

    <link href="https://assets1-my.umbc.edu/images/favicon.ico?1533300947" rel="icon" type="image/x-icon">

    <link
      href="https://assets3-my.umbc.edu/stylesheets/dialog.css?1533301144"
      media="screen"
      rel="stylesheet"
      type="text/css"
    >

    <style>
      .sr-only {
        border: 0;
        clip: rect(0, 0, 0, 0);
        height: 1px;
        margin: -1px;
        overflow: hidden;
        padding: 0;
        position: absolute;
        white-space: nowrap;
        width: 1px;
      }
      #myumbc-logo {
        background-image: url("https://my3.my.umbc.edu/images/sprites.png?1406123226");
        background-position: 0 -192px;
        background-repeat: no-repeat;
        height: 48px;
        width: 217px;
        margin: 12px 0 16px 20px;
      }
    </style>

    <style>
      .login-field {
        margin: 8px 0;
      }
      .login-field input,
      login-field label {
        display: block;
      }
      .login-field input,
      .login-field input[type=password] {
        box-sizing: border-box;
        width: 96%;
        font-size: 1.2em;
        line-height: 1.2em;
        padding: 8px 12px;
      }
      .login-field label {
        font-weight: bold;
      }

      #facebook a:hover,
      #google a:hover {
        opacity: 0.9;
      }

      #dialog-container {
        background-color: #fff;
      }
      #dialog-container h2 {
        font-size: 24px;
        padding: 0;
        margin: 4px 0;
      }
      #dialog-container #login-dialog {
        padding: 8px 20px 20px;
        border-radius: 0;
        margin: 0;
      }
      #login-forms {
        width 100%;
        overflow: auto;
      }
      .login-form-selection {
        background-color: #fff;
        padding: 12px;
      }
      #login-form-other {
        margin-top: 16px;
        border-top: 1px solid #999;
        padding-top: 16px;
      }
      #footer-text {
        color: #666;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #eee;
        font-size: 11px;
      }
      input[type="password"] {
        background-color: #f6f6f6;
        border: 1px solid #999999;
        border-radius: 4px;
        color: #333333;
        font-family: Arial,Helvetica,sans-serif;
        font-size: 13px;
        line-height: 16px;
        padding: 4px;
      }
      #submit {
        font-weight: normal;
        background-color: #3d7a1c;
        color: #fff;
      }
      p.service {
        margin: 4px auto;
      }

      p.service > a, p.service > span {
        display: flex;
        margin: 0 auto;
        width: 230px;
        border-radius: 4px;
        line-height: 24px;
        background-color: #fff;
        border: 1px solid #ddd;
        color: #000;
        text-decoration: none;
        font-size: 16px;
        padding: 8px 8px;
        font-weight: bold;
        height: 24px;
        overflow: hidden;
      }

      p.service .label {
        display: block;
        line-height: 24px;
        vertical-align: middle;
      }

      .service img {
        display: block;
        vertical-align: middle;
        height: 24px;
        width: 24px;
        margin: 0 12px 0 0;
        overflow: hidden;
        border-radius: 0;
      }

      #login-umbc > a {
        background-color: #fdb515;
        border-color: #fdb515;
      }

      #dialog-container {
        width: 320px;
      }
      .login-form-selection {
        padding: 0;
      }
    </style>

    <style>
      #login-myumbc:hover {
        background-color: #ffbb1b;
      }

      html { height: 100%; }

      body {
        background-color: #f5ca5c;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
      }

      #dialog-container {
        margin: auto;
      }
    </style>

  <style>
    #logout-umbc {
      background-color: #d83933;
      color: #fff;
      justify-content: center;
    }

    #logout-umbc:hover {
      background-color: #b52e2a;
      color: #fff;
    }

    #logout-umbc svg {
    fill: #fff;
    }

    #logout-umbc:hover svg {
        fill: #fff;
    }

    #logout-label {
      margin: 0px 0px 0px 8px;
    }
  </style>


  </head>

  <body>
    <main id="dialog-container">
      <h1 class="sr-only">Log In to myUMBC</h1>
      <div id="myumbc-logo" role="img" aria-label="myUMBC logo"></div>

      <div class="section html-content" id="login-dialog">
        <div id="login-forms">

          <?php if (is_user_logged_in()) : ?>

            <div class="login-form-selection" id="logged-in-section">
              <h2>myUMBC Account</h2>
              <p>UMBC Students, Faculty, and Staff</p>
              <p>You are already logged in
                <?php
                  $current_user = wp_get_current_user();
                  if ($current_user->display_name) {
                    echo ' as <strong>' . esc_html($current_user->display_name) . '</strong>';
                  } ?>.
              </p>
              <p class="service">
                <a id="logout-umbc" href="<?php echo esc_url(wp_logout_url()); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M14.08 15.59 16.67 13H7v-2h9.67l-2.59-2.59L15.5 7l5 5-5 5-1.42-1.41ZM19 3c1.10457 0 2 .89543 2 2v4.67l-2-2V5H5v14h14v-2.67l2-2V19c0 1.10457-.89543 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.11.89-2 2-2h14Z"></path>
                  </svg>
                  <span class="label" id="logout-label">Log Out</span>
                </a>
              </p>
            </div>

          <?php else : ?>

            <div class="login-form-selection" id="login-form-myumbc">
              <h2>myUMBC Account</h2>
              <p>UMBC Students, Faculty, and Staff</p>
              <p class="service" id="login-umbc">
                <a href="<?php echo esc_url(add_query_arg('action', 'wp-saml-auth', wp_login_url())); ?>">
                  <img alt="Log In with UMBC" height="24" width="24" src="https://d3uyg54qwz4w8h.cloudfront.net/assets/my4/login-umbc-0a7c460c.png" />
                <span class="label">Log In with UMBC</span>
                </a>
              </p>
            </div>

            <div class="login-form-selection" id="login-form-other">
              <h2>Guest Access</h2>
              <p>Special guests that have been granted access</p>
              <p class="service" id="login-google">
                <a href="/login/google?url=https%3A%2F%2Fmy.umbc.edu%2F">
                  <img alt="Log In with Google" height="24" width="24" src="https://d3uyg54qwz4w8h.cloudfront.net/assets/my4/login-google-ee49770f.png" /> 
                  <span class="label">Log In with Google</span>
                </a>
              </p>
              <p class="service" id="login-microsoft">
                <a href="/login/microsoft?url=https%3A%2F%2Fmy.umbc.edu%2F">
                  <img alt="Log In with Microsoft" height="24" width="24" src="https://d3uyg54qwz4w8h.cloudfront.net/assets/my4/login-microsoft-1f6a4cfb.png" /> 
                  <span class="label">Log In with Microsoft</span>
                </a>
              </p>
              <p class="service" id="login-amazon"><a href="/login/amazon?url=https%3A%2F%2Fmy.umbc.edu%2F">
                <img alt="Log In with Amazon" height="24" width="24" src="https://d3uyg54qwz4w8h.cloudfront.net/assets/my4/login-amazon-c3c04e73.png" /> 
                <span class="label">Log In with Amazon</span>
              </a>
            </p>
            </div>

          <?php endif; ?>

        </div>

        <div id="footer-text">Usage of UMBC computing resources is governed by the 
          <a href="http://www.umbc.edu/policies/pdfs/x-1.00.01%20responsible%20computing%20policy.pdf">UMBC Policy for Responsible Computing</a>.
        </div>
      </div>
    </main>
  </body>
</html>