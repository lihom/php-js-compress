<?php
	error_reporting(0);
	ini_set('display_errors', 0);

	if (isset($_POST['code'])) {
		$code = $_POST['code'];
	}

	if (isset($_POST['host'])) {
		$host = $_POST['host'];
	} else {
		$host = '';
	}

	if (isset($code) && !empty($code)) {
		$host = !empty($host) ? rtrim($host, '/') . '/' : '';
		$result = preg_match_all('/src=(["\'])(.*?\.js)\1/', $code, $match);
		$paths = array();
		if ($result) {
			$_js = $js = $__js =  '';
			foreach ($match[2] as $k => $v) {
				$url = $host . ltrim($v, '/');
				$paths[] = $url;
				$js .= file_get_contents($url);
			}
			$_js = closure_compiler($js);
			$__js = trim($_js);
			$pass = !empty($__js) ? TRUE : FALSE;;
		}
	}

	function closure_compiler($js)
	{
		// REST API arguments
		$apiArgs = array(
			'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
			'output_format' => 'text',
			'output_info' => 'compiled_code'
		);
		
		$args = 'js_code=' . urlencode($js);
		foreach ($apiArgs as $key => $value) {
			$args .= '&' . $key . '=' . urlencode($value);
		}
		
		// API call using cURL
		$call = curl_init();
		curl_setopt_array($call, array(
			CURLOPT_URL => 'http://closure-compiler.appspot.com/compile',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $args,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_FOLLOWLOCATION => 0
		));
		$jscomp = curl_exec($call);
		curl_close($call);
		
		// calculate compression saving
		$reduced = (strlen($js) - strlen($jscomp)) / strlen($js) * 100;

		return $jscomp;
	}
?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>PHP JS Compress</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="container">
			<h1>PHP JS Compress</h1>
			<?php if (isset($_js)) { ?>
				<div class="row">
					<h2>Result</h2>
					<p>
						compress result from those files...
					</p>
					<ul>
						<?php foreach ($paths as $k => $v) { ?>
							<li><small><?php echo $v ?></small></li>
						<?php } ?>
					</ul>
					<div class="form">
						<p>copy and paste to your single script file!</p>
						<?php if ($pass) { ?>
							<textarea style="height:300px;"><?php echo htmlentities($_js) ?></textarea>
						<?php } else { ?>
							<strong>- important</strong>
							<p>your code compress to <a href="http://closure-compiler.appspot.com/" target="_blank">http://closure-compiler.appspot.com/</a> failure, maybe you can try...</p>
							<ol>
								<li><small>use less script tag try again.</small></li>
								<li><small>copy textarea code to <a href="http://closure-compiler.appspot.com/" target="_blank">http://closure-compiler.appspot.com/</a> compress.</small></li>
							</ol>
							<textarea style="height:300px;"><?php echo htmlentities($js) ?></textarea>
						<?php } ?>
						<br />
						<a href="./" class="btn">Back</a>
					</div>
				</div>
			<?php } else { ?>
				<div class="row">
					<h2>Demo</h2>
					<p>
						try enter code and submit!
					</p>
					<p>
						<strong>- Basic example:</strong>
						<br />
						<code>
							<?php echo htmlentities('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-alpha1/jquery.min.js"></script>') ?>
							<br />
							<?php echo htmlentities('<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>') ?>
						</code>
					</p>
					<p>
						<strong>- Relative link example:</strong>
						<br />
						<small>you need input your host (domain or ip on internet), here not support localhost and 127.0.0.1, if you want use on local you can download this source code on <a href="https://github.com/lihom/php-js-compress" target="_blank">github</a>.</small>
						<br />
						<code>
							<?php echo htmlentities('<script src="/ajax/libs/jquery/3.0.0-alpha1/jquery.min.js"></script>') ?>
							<br />
							<?php echo htmlentities('<script src="/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>') ?>
						</code>
					</p>
				</div>

				<div class="row">
					<form id="form" class="form" action="./" method="POST">
						<input id="host" name="host" type="text" placeholder="Host (domain or ip on internet)" value="" />
						<textarea id="code" name="code" placeholder="Script Tags"></textarea>
						<br />
						<input type="reset" value="Cancel" />
						<input type="submit" value="Submit" />
					</form>
				</div>

				<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
				<script>
					$(function() {
						var $code = $('#code'),
							$host = $('#host');

						$host.fadeOut(0);
						$code.bind('keyup', function() {
							var $this = $(this),
								val = $this.val();

							if (/((http|https)\:\/\/[a-zA-Z0-9\-\.]+(:[a-zA-Z0-9]*)?\/?([a-zA-Z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~\*])*)+/g.test(val)) {
								$host.fadeOut(500);
							} else {
								$host.fadeIn(500);
							}
						});
					});
				</script>
			<?php } ?>
		</div>
	</body>
</html>