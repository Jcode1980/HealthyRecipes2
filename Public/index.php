<?php
	ob_start();
	if (file_exists(".offline")) {
		// If the site is marked as down for maintenance then forward to the offline page
		// and go no further. 
		header("location: offline.html");
		exit;
	}
	require "settings.php";
	
	initSession();
	$component = handleRequest("RecipeList");
if (useHTML()) :
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo appName(); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <base href="<?php echo domainName(); ?>/" />
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="js/jquery-2.1.4.min.js"></script>
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		
		<link href="http://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
		<link href="http://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
        
        <meta name="description" itemprop="description" content="<?php echo $component->metaDescription(); ?>">
        <meta name="keywords" itemprop="keywords" content="<?php echo $component->metaKeywords(); ?>">
        
        <link rel="canonical" href="<?php echo $component->conocialURL(); ?>" />
        <?php if (PLController::hasComponentWithName("Feed")) : ?>
        <link rel="alternate" type="application/rss+xml" title="<?php echo appName(); ?> Feed" href="<?php echo domainName(); ?>/Feed" />
        <?php endif; ?>
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel='shortlink' href="<?php echo domainName(); ?>">
        
		<link rel="stylesheet" href="css/shared.css" type="text/css" media="screen" title="shared" charset="utf-8"/>
		<link rel="stylesheet" href="css/components.css" type="text/css" media="screen" title="shared" charset="utf-8"/>
		<?php foreach ($extraCSS as $css) : ?>
			<link rel="stylesheet" href="<?php echo $css; ?>" type="text/css" media="screen" charset="utf-8"/>
		<?php endforeach; ?>
		<script type="text/javascript" src="js/general.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<?php foreach ($extraJS as $js) : ?>
			<script type="text/javascript" src="<?php echo $js; ?>"></script>
		<?php endforeach; ?>
	</head>
	<body id="body">
		<?php $component->appendToResponse(); ?>
	</body>
</html>
<?php else : ?><?php $component->appendToResponse(); ?><?php endif; ?>