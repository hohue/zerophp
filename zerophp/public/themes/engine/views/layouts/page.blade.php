@extends('layouts.html')

@section('body')
    {{ $regions['header'] }}
    {{ $regions['primary menu'] }}

    <div id="content" class="wrapper">
        @if ($breadcrumb)
            <div class="link_sub">{{ $breadcrumb }}</div>
        @endif

        @if ($regions['content top'])
            <div id="content_top">{{ $regions['content top'] }}</div>
        @endif

        <div id="main-content" class="clearfix">
            @if ($regions['left sidebar'] || $regions['user panel sidebar'])
                <div id="left" class="wrapper_w200">
                    {{ $regions['left sidebar'] }}
                    {{ $regions['user panel sidebar'] }}
                </div>
            @endif

            <div id="center"><div id="squeeze">
                @if (1==0 && $page_title)
                    <h1>{{ $page_title }}</h1>
                @endif

                @if (1==0 && $tabs)
                    <div id="menu_tabs" class="tab_title_theme margin_top_7">
                        {{ $tabs }}
                    </div>
                @endif

                <div id="messages" class="lazyAjax" data-url="{{ zerophp_url('esi/response/message') }}"></div>

                {{ $content }}
            </div></div>

            @if ($regions['right sidebar'])
                <div id="right" class="wrapper_w200">
                    {{ $regions['right sidebar'] }}
                </div>
            @endif
        </div>

        {{ $regions['content bottom'] }}
        {{ $regions['secondary menu'] }}
    </div>

    {{ $regions['footer'] }}
    {{ $regions['tags'] }}
@stop



@section('title')
    <title>{{ $title }}</title>
@stop

@section('header')
    {{ $header }}
@stop

@if ($body_class)
    @section('body_tag')
        <body class="{{ $body_class }}">
    @stop
@endif

@section('closure')
    {{ $closure }}
@stop