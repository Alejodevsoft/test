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
        
        <div class="success-message">
          <svg viewBox="0 0 76 76" class="success-message__icon icon-checkmark">
                <circle cx="38" cy="38" r="36"/>
                <path fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M17.7,40.9l10.9,10.9l28.7-28.7"/>
          </svg>
          <h1 class="success-message__title">Configuration completed</h1>
          <button class="btn" onclick="window.location.href = '<?= base_url()?>'">Go to panel</button>
        </div>
        <style>
          .success-message {
            text-align: center;
            max-width: 500px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
          }

          .success-message__icon {
            max-width: 75px;
          }
          .btn{
            background: #a9dfd8 !important;
            color: #171821;
            font-weight: 700;
          }
          .btn:hover{
            background: #73cabe !important;
          }

          .success-message__title {
            color: #3DC480;
            transform: translateY(25px);
            opacity: 0;
            transition: all 200ms ease;
          }
          .active .success-message__title {
            transform: translateY(0);
            opacity: 1;
          }

          .success-message__content {
            color: #FFFFFF;
            transform: translateY(25px);
            opacity: 0;
            transition: all 200ms ease;
            transition-delay: 50ms;
          }
          .active .success-message__content {
            transform: translateY(0);
            opacity: 1;
          }

          .icon-checkmark circle {
            fill: #3DC480;
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

          PathLoader.prototype._draw = function (val) {
              this.el.style.strokeDashoffset = this.strokeLength * (1 - val);
          }

          PathLoader.prototype.setProgress = function (val, cb) {
            this._draw(val);
              if(cb && typeof cb === 'function') cb();
          }

          PathLoader.prototype.setProgressFn = function (fn) {
            if(typeof fn === 'function') fn(this);
          }

          var body = document.body,
              svg = document.querySelector('svg path');

          if(svg !== null) {
              svg = new PathLoader(svg);
              
              setTimeout(function () {
                  document.body.classList.add('active');
                  svg.setProgress(1);
              }, 500);
          }
        </script>
    </div>
</body>
</html>