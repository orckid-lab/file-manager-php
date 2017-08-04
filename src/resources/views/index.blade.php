<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Test</title>
	<meta name="author" content="Orckid Lab"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="icon" type="image/png" href="/img/app/favicon.png"/>
	<link rel="stylesheet" href="/css/app.css"/>
	{{--<link rel="stylesheet" href="/css/keen-ui.min.css">--}}
	@stack('stylesheets')
</head>
<body class="default-template">
<div id="global">
	<main id="main-content">
		<div class="progress-bar">
			<div class="indeterminate"></div>
		</div>
		{{--<file-manager></file-manager>--}}
		<file-manager-modal :multiple="true"></file-manager-modal>
		<file-manager-modal :multiple="false"></file-manager-modal>
	</main>
</div>
</body>
<script>window.Laravel = {"csrfToken": "{{ csrf_token() }}"};</script>
<script src="/js/app.js"></script>
@stack('scripts')
</html>