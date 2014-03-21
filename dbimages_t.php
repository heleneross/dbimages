<?php
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

//	use thumbnails?
$thumbs = true; //**

//thumbnail sizing   
$maxw = 100; //**
$maxh = 100; //**

// don't create images if number of images greater than this limit (0 = unlimited)
$imglimit = 0; //**

// look for galleries
$galleries = true; //**
;
?>
	<!DOCTYPE html>
	<head><title>Find</title>
		<style>
			table {
				border: 1px solid black;
				border-collapse: collapse;
			}

			th, td {
				border: 1px solid silver;
				padding: 5px;
			}

			li div {
				display: inline-block;
			}

			li div img {
				padding: 5px;
				vertical-align: middle;
			}

			img.table {
				margin: auto;
				display: block;
			}
		</style>
	</head>
<body>
<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">[Home]</a>

<h1>Images</h1>

<?php
set_time_limit(0);
$basename = basename($_SERVER['PHP_SELF']);

function f_images($a)
{
	$a = strtolower($a);
	return preg_match('#(jpg|gif|png|jpeg)$#', $a); //**
}

function f_folders($a)
{
	$dir = $_GET["dir"] ? $_GET["dir"] . '/' : '';
	return is_dir($dir . $a);
}

$currdir = getcwd();
require('../configuration.php');
$config = new JConfig();

$dir = $_GET["dir"] ? $_GET["dir"] : $currdir;

$ds = DIRECTORY_SEPARATOR;

$foundfiles = scandir($dir);

echo '<p><strong>' . ($dir == $currdir ? 'images' : 'images/' . $dir) . '</strong></p>';
// now set $dir to something useful for links
$dir = ($dir == $currdir ? '' : $dir . '/');

// filter out unwanted files
$files = array_filter($foundfiles, 'f_images');

$folders = preg_grep('#\.#', $foundfiles, PREG_GREP_INVERT);
$folders = array_filter($folders, 'f_folders');

$prefix = $config->dbprefix;
// reduce the number of tables to search
//**
$db_excluded = array(
	$prefix . 'ak_profiles',
	$prefix . 'ak_stats',
	$prefix . 'ak_storage',
	$prefix . 'associations',
	$prefix . 'backups',
	$prefix . 'banner_clients',
	$prefix . 'banner_tracks',
	$prefix . 'banners',
	$prefix . 'content_frontpage',
	$prefix . 'content_rating',
	$prefix . 'cookieconfirm_blockelements',
	$prefix . 'cookieconfirm_log',
	$prefix . 'cookieconfirm_profiles',
	$prefix . 'cookieconfirm_storage',
	$prefix . 'core_log_searches',
	$prefix . 'f2c_fieldtype',
	$prefix . 'f2c_translation',
	$prefix . 'facileforms_compmenus',
	$prefix . 'facileforms_config',
	$prefix . 'facileforms_integrator_criteria_fixed',
	$prefix . 'facileforms_integrator_criteria_form',
	$prefix . 'facileforms_integrator_criteria_joomla',
	$prefix . 'facileforms_integrator_items',
	$prefix . 'facileforms_integrator_rules',
	$prefix . 'facileforms_records',
	$prefix . 'facileforms_scripts',
	$prefix . 'finder_filters',
	$prefix . 'finder_links',
	$prefix . 'finder_links_terms0',
	$prefix . 'finder_links_terms1',
	$prefix . 'finder_links_terms2',
	$prefix . 'finder_links_terms3',
	$prefix . 'finder_links_terms4',
	$prefix . 'finder_links_terms5',
	$prefix . 'finder_links_terms6',
	$prefix . 'finder_links_terms7',
	$prefix . 'finder_links_terms8',
	$prefix . 'finder_links_terms9',
	$prefix . 'finder_links_termsa',
	$prefix . 'finder_links_termsb',
	$prefix . 'finder_links_termsc',
	$prefix . 'finder_links_termsd',
	$prefix . 'finder_links_termse',
	$prefix . 'finder_links_termsf',
	$prefix . 'finder_taxonomy',
	$prefix . 'finder_taxonomy_map',
	$prefix . 'finder_terms',
	$prefix . 'finder_terms_common',
	$prefix . 'finder_tokens',
	$prefix . 'finder_tokens_aggregate',
	$prefix . 'finder_types',
	$prefix . 'jckplugins',
	$prefix . 'jcktoolbarplugins',
	$prefix . 'jcktoolbars',
	$prefix . 'jev_users',
	$prefix . 'jevents_categories',
	$prefix . 'jevents_catmap',
	$prefix . 'jevents_exception',
	$prefix . 'jevents_icsfile',
	$prefix . 'jevents_repbyday',
	$prefix . 'jevents_repetition',
	$prefix . 'jevents_rrule',
	$prefix . 'languages',
	$prefix . 'menu_types',
	$prefix . 'messages',
	$prefix . 'messages_cfg',
	$prefix . 'modules_menu',
	$prefix . 'newsfeeds',
	$prefix . 'overrider',
	$prefix . 'schemas',
	$prefix . 'searchadvanced_areamembers',
	$prefix . 'searchadvanced_areas',
	$prefix . 'searchadvanced_authremotes',
	$prefix . 'searchadvanced_backend_filters',
	$prefix . 'searchadvanced_cache',
	$prefix . 'searchadvanced_index',
	$prefix . 'searchadvanced_log_searches',
	$prefix . 'searchadvanced_log_suggest',
	$prefix . 'searchadvanced_modifiers',
	$prefix . 'searchadvanced_remotes',
	$prefix . 'searchadvanced_search_saves',
	$prefix . 'session',
	$prefix . 'sh404sef_aliases',
	$prefix . 'sh404sef_metas',
	$prefix . 'sh404sef_pageids',
	$prefix . 'shlib_consumers',
	$prefix . 'shlib_resources',
	$prefix . 'update_categories',
	$prefix . 'update_sites',
	$prefix . 'update_sites_extensions',
	$prefix . 'updates',
	$prefix . 'user_notes',
	$prefix . 'user_profiles',
	$prefix . 'user_usergroup_map',
	$prefix . 'usergroups',
	$prefix . 'viewlevels',
	$prefix . 'weblinks',
	$prefix . 'wf_profiles',
	$prefix . 'xmap_items'
);

// images you do not want to check for whatever reason
//**
$excluded_images = array('fake.gif');

$files = array_diff($files, $excluded_images);

if ($imglimit > 0 && count($files) > $imglimit)
{
	$thumbs = false;
}

$db = new PDO('mysql:host=' . $config->host . ';dbname=' . $config->db . ';charset=utf8', $config->user, '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$stmt = $db->query('SHOW TABLES');
$db_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$db_tables = array_diff($db_tables, $db_excluded);

$db_wheres = array();
foreach ($db_tables as $table)
{
	$sql = 'SHOW FIELDS IN ' . $table . ' WHERE (type LIKE "%text%" OR type LIKE "%varchar%")';
	$stmt = $db->query($sql);
	$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
	if (!empty($cols))
	{
		$db_wheres[$table] = ' WHERE (`' . implode($cols, '` LIKE ? OR `') . '` LIKE ?)';
	}
}
$notfound = array();
$found = array();

foreach ($db_wheres as $key => $value)
{
	$sql = 'SELECT COUNT(*) FROM ' . $key . $value;
	//$sql = 'SELECT * FROM '.$key. $value;
	$st = $db->prepare($sql);
	$num = substr_count($sql, '?');
	foreach ($files as $file)
	{
		$params = array_fill(0, $num, '%' . $dir . $file . '%');
		$st->execute($params);
		$rows = $st->fetchColumn();
		//$rows = $st->fetchAll(PDO::FETCH_ASSOC);

		if ($rows > 0)
		{
			if ($thumbs)
			{
				//$found[$file] = '<tr><td><img src="thumb.php?file='.$dir.$file.'&maxw='.$maxw.'&maxh='.$maxh.'"  class="table" /></td><td><a href="'.$dir.$file.'" target="_blank">'.$file.'</a></td><td>'.str_replace($prefix,'',$key).'</td><td>'.$rows.'</td></tr>';
				$found[$file] = '<tr><td><img src="data:image/jpeg;base64,' . getimage($dir . $file, $maxw, $maxh) . '" class="table" /></td><td><a href="' . $dir . $file . '" target="_blank">' . $file . '</a></td><td>' . str_replace($prefix, '', $key) . '</td><td>' . $rows . '</td></tr>';
			}
			else
			{
				$found[$file] = '<tr><td><a href="' . $dir . $file . '" target="_blank">' . $file . '</a></td><td>' . str_replace($prefix, '', $key) . '</td><td>' . $rows . '</td></tr>';
			}
		}

		unset($params);
	}
}
$files = array_flip($files);
echo '<table><tbody>';
echo implode($found);
echo '</tbody></table><p>Number of images in db: ' . count($found) . '</p><h2>Images not found in db</h2><ul>';
$notfound = array_diff_key($files, $found);
foreach ($notfound as $file => $value)
{
	if ($thumbs)
	{
		//echo '<li><div><img src="thumb.php?file='.$dir.$file.'&maxw='.$maxw.'&maxh='.$maxh.'" /><a href="'.$dir.$file.'" target="_blank">'.$file.'</a></div></li>';
		echo '<li><div><img src="data:image/jpeg;base64,' . getimage($dir . $file, $maxw, $maxh) . '" /><a href="' . $dir . $file . '" target="_blank">' . $file . '</a></div></li>';
	}
	else
	{
		echo '<li><a href="' . $dir . $file . '" target="_blank">' . $file . '</a></li>';
	}
}
echo '</ul><p>Number of images not found in db: ' . count($notfound) . '</p><h2>Folders</h2>';

echo '<ul>';
if ($galleries)
{
	$sql = "SELECT COUNT(*) FROM `" . $prefix . "content` WHERE (`introtext` like ? or `fulltext` like ?)";
	$st = $db->prepare($sql);
	foreach ($folders as $folder)
	{
		$params = array("%{gallery}" . $dir . $folder . "{/gallery}%", "%{gallery}" . $dir . $folder . "{/gallery}%");
		$st->execute($params);
		$rows = $st->fetchColumn();
		if ($rows)
		{
			echo '<li><a href="' . $basename . '?dir=' . $dir . $folder . '">' . $folder . '</a> (Gallery)</li>';
		}
		else
		{
			echo '<li><a href="' . $basename . '?dir=' . $dir . $folder . '">' . $folder . '</a></li>';
		}
	}
}
else
{
	foreach ($folders as $folder)
	{
		echo '<li><a href="' . $basename . '?dir=' . $dir . $folder . '">' . $folder . '</a></li>';
	}
}
echo '</ul>';

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo "<p>This page was created in " . round($totaltime, 2) . " seconds</p>";
echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '">[Home]</a>';
echo '<p>&nbsp;</p>';
echo '</body></html>';

function getimage($sImagePath, $iMaxWidth = null, $iMaxHeight = null)
{
// http://www.webgeekly.com/tutorials/php/how-to-create-an-image-thumbnail-on-the-fly-using-php/
// Marc von Brockdorff 

	$img = null;

	$sExtension = strtolower(end(explode('.', $sImagePath)));
	if ($sExtension == 'jpg' || $sExtension == 'jpeg')
	{

		$img = @imagecreatefromjpeg($sImagePath)
			or null;

	}
	else if ($sExtension == 'png')
	{

		$img = @imagecreatefrompng($sImagePath)
			or null;

	}
	else if ($sExtension == 'gif')
	{

		$img = @imagecreatefromgif($sImagePath)
			or null;

	}

	if ($img)
	{

		$iOrigWidth = imagesx($img);
		$iOrigHeight = imagesy($img);

		// Get scale ratio

		$fScale = min($iMaxWidth / $iOrigWidth,
			$iMaxHeight / $iOrigHeight);

		if ($fScale < 1)
		{

			$iNewWidth = floor($fScale * $iOrigWidth);
			$iNewHeight = floor($fScale * $iOrigHeight);

			$tmpimg = imagecreatetruecolor($iNewWidth,
				$iNewHeight);

			imagecopyresampled($tmpimg, $img, 0, 0, 0, 0,
				$iNewWidth, $iNewHeight, $iOrigWidth, $iOrigHeight);

			imagedestroy($img);
			$img = $tmpimg;
		}

		ob_start();
		//header("Content-type: image/jpeg");
		imagejpeg($img);
		$outimage = ob_get_clean();
		return base64_encode($outimage);
	}
	return null;
}


