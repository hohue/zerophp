<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @section('title')
      <title>ChoVip.vn</title>
    @show

    <link rel="stylesheet" type="text/css" href="/themes/engine/css/style.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/assets/jquery.ui/css/smoothness/jquery.ui.min.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/assets/colorbox/colorbox.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/themes/chovip/css/css_reset.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/themes/chovip/css/style.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/themes/chovip/css/bg_gradient.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/themes/chovip/css/style_ak.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/themes/chovip/css/mytheme.responsive.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="/assets/datatables/media/css/jquery.dataTables.min.css">

    <!--[if lt IE 9]>
      <script src="/assets/html5shiv/html5shiv.min.js"></script>
      <script src="/assets/respond/respond.min.js"></script>
      <script src="/themes/chovip/js/selectivizr-min.js" type="text/javascript"></script>
      <script src="/themes/chovip/js/PIE.js" type="text/javascript"></script>
    <![endif]-->

    <link href1='http://fonts.googleapis.com/css?family=Roboto:700&subset=latin,vietnamese' rel='stylesheet' type='text/css'>

    @section('header')
    @show
  </head>
  @section('body_tag')
    <body>
  @show
    @yield('body')

    <script type="text/javascript" src="/assets/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/fw/fw.js"></script>

    @section('closure')
    @show
    
    <script type="text/javascript" src="/assets/once/jquery.once.min.js"></script>
    <script type="text/javascript" src="/assets/lazyload/jquery.lazyload.min.js"></script>
    <script type="text/javascript" src="/assets/validate/jquery-validate.min.js"></script>
    <script type="text/javascript" src="/assets/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="/assets/fw/ajax.js"></script>
    <script type="text/javascript" src="/assets/fw/form.js"></script>
    <script type="text/javascript" src="/assets/fw/lazyload.js"></script>
    <script type="text/javascript" src="/assets/fw/validate-extend.js"></script>
    <script type="text/javascript" src="/assets/fw/validate.js"></script>
    <script type="text/javascript" src="/assets/fw/tinymce.config.js"></script>

    <script type="text/javascript" src="/assets/jquery.ui/js/jquery.ui.min.js"></script>

    <script type="text/javascript" src="/assets/colorbox/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="/assets/fw/colorbox.js"></script>

    <script type="text/javascript" src="/assets/datatables/media/js/jquery.dataTables.min.js" charset="utf8"></script>
    <script type="text/javascript" src="/assets/fw/datatables.js"></script>
    
    <script type="text/javascript" src="/themes/chovip/js/custom.js"></script>
  </body>
</html>