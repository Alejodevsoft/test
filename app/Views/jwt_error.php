<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="login-body">
  <div class="container">
    <h1>Verifying the connection with docusign</h1>
    <div class="error-message">
      <svg viewBox="0 0 76 76" class="error-message__icon icon-checkmark">
        <circle cx="38" cy="38" r="36" />
        <path fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M25 25 L51 51 M51 25 L25 51" />
      </svg>
      <h1 class="error-message__title">An error has occurred, please verify your credentials</h1>
      <button class="btn" onclick="window.location.href = '<?= base_url() ?>'">Go back</button>
    </div>
    <style>
      .error-message {
        text-align: center;
        max-width: 500px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }

      .error-message__icon {
        max-width: 75px;
      }

      .btn {
        background: #DD7788 !important;
        color: #000000;
        font-weight: 700;
      }

      .btn:hover {
        background: #BB5566 !important;
      }

      .error-message__title {
        color: #FF8899;
        transform: translateY(25px);
        opacity: 0;
        transition: all 200ms ease;
      }

      .active .error-message__title {
        transform: translateY(0);
        opacity: 1;
      }

      .error-message__content {
        color: #FFFFFF;
        transform: translateY(25px);
        opacity: 0;
        transition: all 200ms ease;
        transition-delay: 50ms;
      }

      .active .error-message__content {
        transform: translateY(0);
        opacity: 1;
      }

      .icon-checkmark circle {
        fill: #FF8899;
        transform-origin: 50% 50%;
        transform: scale(0);
        transition: transform 200ms cubic-bezier(0.22, 0.96, 0.38, 0.98);
      }

      .icon-checkmark path {
        transition: stroke-dashoffset 350ms ease;
        transition-delay: 100ms;
      }

      .active .icon-checkmark circle {
        transform: scale(1);
      }

      .success-button__content {
        transform: translateY(25px);
        opacity: 0;
        transition: all 200ms ease;
        transition-delay: 50ms;
      }

      .active .success-button__content {
        transform: translateY(0);
        opacity: 1;
      }
    </style>
    <script>
      function PathLoader(el) {
        this.el = el;
        this.strokeLength = el.getTotalLength();

        // set dash offset to 0
        this.el.style.strokeDasharray =
          this.el.style.strokeDashoffset = this.strokeLength;
      }

      PathLoader.prototype._draw = function(val) {
        this.el.style.strokeDashoffset = this.strokeLength * (1 - val);
      }

      PathLoader.prototype.setProgress = function(val, cb) {
        this._draw(val);
        if (cb && typeof cb === 'function') cb();
      }

      PathLoader.prototype.setProgressFn = function(fn) {
        if (typeof fn === 'function') fn(this);
      }

      var body = document.body,
        svg = document.querySelector('svg path');

      if (svg !== null) {
        svg = new PathLoader(svg);

        setTimeout(function() {
          document.body.classList.add('active');
          svg.setProgress(1);
        }, 500);
      }
    </script>
  </div>
</body>

</html>