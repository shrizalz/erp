<!DOCTYPE html>
<html class="no-js css-menubar" lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta name="description" content="bootstrap admin template">
  <meta name="author" content="">
    <title>404 | Citra Alti</title>
    {!! Html::style('topicon/assets/images/apple-touch-icon.png') !!}
    {!! Html::style('topicon/assets/images/favicon.ico') !!}
    <!-- Stylesheets -->
    {!! Html::style('global/css/bootstrap.min.css') !!}
    {!! Html::style('global/css/bootstrap-extend.min.css') !!}
    {!! Html::style('topicon/assets/css/site.min.css') !!}
    <!-- Plugins -->
    {!! Html::style('global/vendor/animsition/animsition.css') !!}
    {!! Html::style('global/vendor/asscrollable/asScrollable.css') !!}
    {!! Html::style('global/vendor/switchery/switchery.css') !!}
    {!! Html::style('global/vendor/intro-js/introjs.css') !!}
    {!! Html::style('global/vendor/slidepanel/slidePanel.css') !!}
    {!! Html::style('global/vendor/flag-icon-css/flag-icon.css') !!}
    {!! Html::style('topicon/assets/examples/css/pages/errors.css') !!}
    <!-- Fonts -->
    {!! Html::style('global/fonts/web-icons/web-icons.min.css') !!}
    {!! Html::style('global/fonts/brand-icons/brand-icons.min.css') !!}
    {!! Html::style('global/fonts/material-design/material-design.min.css') !!}
    {!! Html::style('http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic') !!}
  <!--[if lt IE 9]>
    <script src="../../../global/vendor/html5shiv/html5shiv.min.js"></script>
    <![endif]-->
  <!--[if lt IE 10]>
    <script src="../../../global/vendor/media-match/media.match.min.js"></script>
    <script src="../../../global/vendor/respond/respond.min.js"></script>
    <![endif]-->
  <!-- Scripts -->
    <style>
        .page {
            max-width: 100%;
        }
    </style>
    {!! Html::script('global/vendor/modernizr/modernizr.js') !!}
    {!! Html::script('global/vendor/breakpoints/breakpoints.js') !!}
  <script>
  Breakpoints();
  </script>
</head>
<body class="page-error page-error-404 layout-full">
  <!--[if lt IE 8]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->
  <!-- Page -->
  <div class="page animsition vertical-align text-center" data-animsition-in="fade-in"
  data-animsition-out="fade-out">
    <div class="page-content vertical-align-middle">
      <header>
        <h1 class="animation-slide-top">404</h1>
        <p>Page Not Found !</p>
      </header>
      <p class="error-advise">YOU SEEM TO BE TRYING TO FIND YOUR WAY HOME</p>
      <a class="btn btn-primary btn-round" href="{{ URL::to('/') }}">BACK HOME</a>
      <footer class="page-copyright">
         <p>&copy; {{ date('Y') }}. CRAFTED WITH <i class="red-600 icon md-favorite"></i> BY STARLING.</p>
      </footer>
    </div>
  </div>
  <!-- End Page -->
  <!-- Core  -->
    <!-- Core  -->
     {!! Html::script('global/vendor/jquery/jquery.min.js') !!}
     {!! Html::script('global/vendor/bootstrap/bootstrap.js') !!}
     {!! Html::script('global/vendor/animsition/animsition.js') !!}
     {!! Html::script('global/vendor/asscroll/jquery-asScroll.js') !!}
     {!! Html::script('global/vendor/mousewheel/jquery.mousewheel.js') !!}
     {!! Html::script('global/vendor/asscrollable/jquery.asScrollable.all.js') !!}
     {!! Html::script('global/vendor/ashoverscroll/jquery-asHoverScroll.js') !!}
    <!-- Plugins -->
     {!! Html::script('global/vendor/switchery/switchery.min.js') !!}
     {!! Html::script('global/vendor/intro-js/intro.js') !!}
     {!! Html::script('global/vendor/screenfull/screenfull.js') !!}
     {!! Html::script('global/vendor/slidepanel/jquery-slidePanel.js') !!}
    <!-- Scripts -->
     {!! Html::script('global/js/core.js') !!}
     {!! Html::script('topicon/assets/js/site.js') !!}
     {!! Html::script('topicon/assets/js/sections/menu.js') !!}
     {!! Html::script('topicon/assets/js/sections/menubar.js') !!}
     {!! Html::script('topicon/assets/js/sections/sidebar.js') !!}
     {!! Html::script('global/js/configs/config-colors.js') !!}
     {!! Html::script('topicon/assets/js/configs/config-tour.js') !!}
     {!! Html::script('global/js/components/asscrollable.js') !!}
     {!! Html::script('global/js/components/animsition.js') !!}
     {!! Html::script('global/js/components/slidepanel.js') !!}
     {!! Html::script('global/js/components/switchery.js') !!}
  <script>
  (function(document, window, $) {
    'use strict';
    var Site = window.Site;
    $(document).ready(function() {
      Site.run();
    });
  })(document, window, jQuery);
  </script>
</body>
</html>
