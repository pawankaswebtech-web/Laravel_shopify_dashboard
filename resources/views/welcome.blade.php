@extends('shopify-app::layouts.default')

@section('content')
    <p>You are: {{ $shopDomain ?? Auth::user()->name }}</p>

    <ui-title-bar title="Welcome">
        <button variant="primary" onclick="console.log('Primary action')">
            Primary action
        </button>
    </ui-title-bar>
@endsection