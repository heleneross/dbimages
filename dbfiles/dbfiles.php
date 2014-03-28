<?php 
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime;
$scriptname = $_SERVER['SCRIPT_NAME']; //eg. /files/dbfiles.php
$basename = basename($_SERVER['PHP_SELF']); //eg. dbfiles.php
$dirname = $dirname = basename(__DIR__); //eg. files
?> 
<!DOCTYPE html>
<head><title>Find</title>
<style>
table{border: 1px solid black; border-collapse:collapse;}
th, td {border: 1px solid silver;padding:5px;}
ul.thumb {list-style-type:none;}
li div {display:inline-block;} 
li div img{vertical-align: middle;float:left;padding:3px;}
img.table {margin:auto;display:block;}
img.folder {vertical-align: text-bottom;padding-bottom:2px;}
</style>
</head>
<body>
<a href="<?php echo $scriptname; ?>">[Home]</a>
<h1>Files</h1>

<?php
set_time_limit(0);
 
$thumbs = true;
function f_images($a)
{
  $a = strtolower($a);
  return preg_match('#(pdf|ppt|dot|doc|xls|mp3)$#',$a); //**
}

function f_folders ($a)
{
  $dir = $_GET["dir"]?$_GET["dir"].'/':'';
  return is_dir($dir.$a);
}

$currdir = getcwd ();
require ('../configuration.php');
$config = new JConfig();

$dir = $_GET["dir"]?$_GET["dir"]:$currdir;

$ds = DIRECTORY_SEPARATOR;

$foundfiles = scandir($dir);

echo '<p><strong>'.($dir == $currdir?$dirname:$dirname.'/'.$dir).'</strong></p>';
// now set $dir to something useful for links
$dir = ($dir == $currdir?'':$dir . '/');

// filter out unwanted files
$files = array_filter($foundfiles,'f_images');

$folders = preg_grep('#\.#',$foundfiles,PREG_GREP_INVERT);
$folders = array_filter($folders,'f_folders');

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

// files you do not want to check for whatever reason
//**
$excluded_images = array('fake.doc');

$files = array_diff($files,$excluded_images);


$db = new PDO('mysql:host='.$config->host.';dbname='.$config->db.';charset=utf8', $config->user, '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$stmt = $db->query('SHOW TABLES');
$db_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$db_tables = array_diff($db_tables,$db_excluded);

$db_wheres = array();
foreach($db_tables as $table)
{
   $sql = 'SHOW FIELDS IN '.$table.' WHERE (type LIKE "%text%" OR type LIKE "%varchar%")';
   $stmt = $db->query($sql);
   $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
   if(!empty($cols))
   {
      $db_wheres[$table] = ' WHERE (`' . implode($cols,'` LIKE ? OR `') . '` LIKE ?)';
   }
}
$notfound = array();
$found = array();

foreach ($db_wheres as $key=>$value)
{
    $sql = 'SELECT COUNT(*) FROM '.$key. $value;
    //$sql = 'SELECT * FROM '.$key. $value;
    $st = $db->prepare($sql);
    $num = substr_count($sql,'?');
    foreach($files as $file)
    {
        $urlencoded = false;
        $params = array_fill(0,$num,'%'.$dirname.'/'.$dir.$file.'%');
        $st->execute($params);
        $rows = $st->fetchColumn();
        //$rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if($rows == 0 && rawurlencode($file) != $file)
        {
          $params = array_fill(0,$num,'%'.$dirname.'/'.$dir.rawurlencode($file).'%');
          $st->execute($params);
          $rows = $st->fetchColumn();
          $urlencoded = true;
        }
        if($rows > 0)
        {
          if($thumbs)
          {
              $ext = strtolower(end(explode('.',$file)));
          	  $found[$file] = '<tr><td><img alt="'.$ext.'" src="'.geticon($ext).'"  class="table" /></td><td><a href="'.$dir.$file.'" target="_blank">'.$file.'</a>'.($urlencoded ? ' *' : '').'</td><td>'.str_replace($prefix,'',$key).'</td><td>'.$rows.'</td></tr>';
       		}
           else
           {
       		   $found[$file] = '<tr><td><a href="'.$dir.$file.'" target="_blank">'.$file.'</a></td><td>'.str_replace($prefix,'',$key).'</td><td>'.$rows.'</td></tr>';
			     }
        }

        unset($params);
    }
}
$files = array_flip($files);
echo '<table><tbody>';
echo implode($found);
echo '</tbody></table><p>Number of files in db: '.count($found).'</p><h2>Files not found in db</h2>';
echo $thumbs ? '<ul class="thumb">' : '<ul>';
$notfound = array_diff_key($files,$found);
foreach($notfound as $file=>$value)
{
	$illegals = (preg_match('#(\'|\s|\&)#',$file))? ' *' : '';
  if ($thumbs){
  		$ext = strtolower(end(explode('.',$file)));
      echo '<li><div><img alt="'.$ext.'" src="'.geticon($ext).'"  class="table" /><a href="'.$dir.$file.'" target="_blank">'.$file.'</a>'.$illegals.'</div></li>';
	}else{
		echo '<li><a href="'.$dir.$file.'" target="_blank">'.$file.'</a> '.$illegals.'</li>';
	}
}
echo '</ul>' . ($illegals ? '<p>* illegal characters or spaces in filename, please check manually in db</p>' : '').'<p>Number of files not found in db: '.count($notfound).'</p><h2>Folders</h2>';

echo $thumbs ? '<ul class="thumb">' : '<ul>';
foreach ($folders as $folder)
{
  if($thumbs)
  {
    echo '<li><img alt="" src="'.geticon('folder').'" class="folder" /> <a href="'.$scriptname.'?dir='.$dir.$folder.'">'.$folder.'</a></li>';
  }
  else
  {
    echo '<li><a href="'.$scriptname.'?dir='.$dir.$folder.'">'.$folder.'</a></li>';
  }
}
echo '</ul>';
                                            
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "<p>This page was created in ".round($totaltime,2)." seconds</p>"; 
   echo '<a href="'. $scriptname.'">[Home]</a>'; 
   echo '<p>&nbsp;</p>';
echo '</body></html>';

function geticon($ext)
{
// generate more icons at http://webcodertools.com/imagetobase64converter/Create
$icons = array(
'file' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAABYlBMVEUgIGVZgcZYgsVdhMRehMRhiMNiicNlisJni8FpjMFsjsBujr9ykL9ykb92k716lbt7lbx+l7qCmryFnb6IoMCLosKMosKPpcSRp8aSqMaVq8iXrMmarsudsM2dsc2htM6jttGkttGnudOpu9SqvNWuvtevwNizw9rU5PrW5PrW5frW5fvX5fvY5frY5vvZ5vvZ5/va5/ra5/vb5/vc6Pvc6Pzc6fvc6fzd6fvd6fze6fve6vve6vzf6vvf6vzf6/zg6/vg6/zh6/zg7Pzh7Pvh7Pzi7Pvi7Pzi7fzj7fvj7fzk7fzj7vzj7v3k7vzl7vzl7v3l7/zl7/3m7/zm7/3n7/zn8Pzn8P3o8Pzo8P3p8P3o8f3p8f3q8f3r8f3q8vzr8v3r8/3s8/3t8/3t9P3t9P7u9P3u9P7v9P3u9f7v9f3v9f7w9f7w9v3x9v3x9/7y9/3y9/7z9/70+P7///9VgMh2qvBUAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfWCxgAGCK9tcJOAAAA50lEQVQY02NgYGBQV1aQk5YQFRYUZIAAteLiooLc7DTBEqiIanFhXk5GarJgCVREpTA/Nz0lKQEoABFRysnJTE6KjxEEAZCAYlZaUmJsTGRwgJcHWEA+LSkhNjo8xN/XwwUsIJuUEBMRGuzn4+rqyA8SkImLigz39/Nydba35gMJSEWGB/t7e7g6WluZ84AEJMMCA7zdXR2sLUyMuEAC4kH+Hu5O9jZmJkYGHCABMW9PN0d7G1MTQz0dNpCAiIerva2libGhvq4WC0hA2NHOCqhdX09bU4MRJCAkIMDHy83JzsrMVFoKAB5KMY+XnxuDAAAAAElFTkSuQmCC", 
'doc'=>"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAABLFBMVEUgIH0IL4sFNoIIPIgGQpsoPm0JS7IhTJo6WpgyW7YqYrc4Ya5AZrQ6abhEarlNaqdCbsRVbaRJdMxddatSd8pVe85VgMhffcNYftFZgcZYgsVnfrNdhMRggc1ehMRhiMNiicNlisJni8F3ipxpjMFnitVsjsBujr9ykL9ykb92k713kdV6lbt7lbx+l7p8l9iCmryFnb6DnNqIoMCAnuaLosKMosKLpN2Rp8aJpemXrMmQrtaTq+adsc2jttGpu9StutCvvN+vwNiqvvGzw9q3yPTJ0uLW3OrS4vrU4/rc4vTW5PrX5fvZ5vvb5/vc6Pve6fvg6vzh7Pzj7fzl7vzm7/zo8P3q8f3r8v3w8vrt8/3v9P3w9v7y9/70+P71+f73+v75+/////8BMngx1mg0AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfWBhAMAA2gQXJ0AAAA4ElEQVQY0z2O11bCQBBAh2VdmkZjQSzYICggqxRF3NilhRYgiAVQh///BzKi3re595yZAQB4vru9ub66yEoJcx6/vz4n49GHRDKmWSqcGZH1leC7RDIm/iJeXeGa0lMuWo1aelEMJQEFK7dpadZyTLwMnH5PQhrT2oOv6K9wmrsSDNzR1lhgAbmjVNc+hQ3U/WXG7tHrdruTglX0hN7YIiLrKdVpn8DS39mp29utBASFEJxzL5vaSrWaR/T65c9+6s3GAYm801dzGvU9Etn/Xq9tkTjPZFLJ4/jh/u52ODwDUNw0qTJOYJQAAAAASUVORK5CYII=",
'pdf'=>"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAABPlBMVEVtCQJjAACMAAiUAABzGBitAAi1AADGAADOCAicISHOEBDnCBDWGBj3EBD3GBjOKTGcQkK9OTn3KSneOTm9SkrvOTn/MTnOSkq9UlreSkrvQkrnSkpVgMjvSkpZgcZYgsVdhMRehMTvUlLvUlphiMNiicPnWlplisLnWmNni8HWY2tpjMFsjsBujr9ykL9ykb92k716lbt7lbx+l7rnc3OCmryFnb6IoMD/c3Pve3uLosKMosLehISPpcSRp8aSqMbnjIyVq8iXrMmZrcqcsMydsc2jttHvnJypu9SsvtavwNizw9rvra3etb3v1tbS4vrU4/rW5PrX5fvZ5vvb5/vc6Pve6fvg6vzh7Pzj7fzl7vzn7/fm7/zo8P3q8f3r8v3t8/3v9P3w9v7y9/70+P71+f73+v75+/////9aAACwyNz9AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfWBhAMAijZc8SxAAAA0klEQVQY02NgYGDw9nBzdbKzMjM2ZoAAr/S01JTkpETjDKiIJ4SfYJwBEpEQERTXUFNWk+KPBwoARSxkxYT4+Hj5uFnjjEGAQZSHi4udg52VjSk2JioywpiBmTGTRQAIOAVA/HCwsS6JCfFxkmB+mCFIwBnI9xN2B/FDDUACjvFxsZbRDj5AfogeSMA+LtZP2sdHUlrJJlgHJGAbG6PmC1LvqxWkCRKwjtFwAPFDgoMCVUEC5iYmMH6AIkjALBLO95cDCZgaGRno62qrqyjIy8gAAGduNQKHw8HXAAAAAElFTkSuQmCC",
'xls' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAABdFBMVEVveCo0TRk1TRg0Uhc0Uxc0WBU0XhM0XhQ1Xho0YxE0ZBI2YxszaQ8yaCgzbQ40bQ47aB4zcA06bx45cB47cSI6dxk8dx8+ex46fSc/fSFCgCVGhCxHhS1CiTVHiDtNjDZgflxPjjlIkj9Tk0BSl0hVgMhYmEdZgcZYgsVamklemFVdhMRehMRYnlJhiMNiicNbolhfoVNlisJhoFhholRni8FpjMFwm21sjsBujr9lp1tkqGBykL9ykb9nql92k71prWJqrGV0pXB6lbt7lbx+l7ptsGeCmryFnb6HqYWIoMCLosKMosKRp8acsJuXrMmQrtaQvYuhs5+dsc2Two6TxI2WxJGjttGaxpapu9SvwNizw9rA0cDM2svP3c/W4tbS4vrU4/rW5PrX5fvZ5vvf6d/b5/vc6Pve6fvg6vzl7OXh7Pzj7fzl7vzm7/zo8P3q8f3r8v3t8/3v9P3w9v7y9/70+P71+f73+v75+/////81SRob6qKVAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfWBhAMAg4LfkFMAAAA4ElEQVQY02NgYGCIjgwP9vf19nB1ZYCAqMqK8rLSkmLXKpCIm4OVoaayvIyUeJFrFUjErQoKBAuBAkARtxBrlapYLS0/vgJXEGCwywp1lHUylovnyc/Lzcl2ZTCpioswkOCNqeIE8TNdGdSqAnXDDETMU9lzAwIyM5wZFAPFhLgDlQSCWIHyGen2DNIgGzyFORSYswMC0tNsGCRh1jIB5dNSLBhEBfl5uNhYWRirMwICUpJNQU73AZsPkk9O0gcJeOXmBEBAUqIOSMADLp+YoA4ScHdxsbe1NDPS09ZQVQUAym9GeqIKMRMAAAAASUVORK5CYII=",
'ppt' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAABhlBMVEUiCQJ2GhR8IBiAJByDKB6ILCGGKy2NMSWQNCeSNS2SNi6RNzCROjOROzSZPCywOS+iRDOkRzSvRzmqTDivUDu8Ty+yUz2dXUi3WEG4WkKwX0S9XkXZWSu/YUjAYUhVgMhZgcZYgsXDZUvDZktdhMRehMRhiMPHa09iicPIa09lisJni8FpjMHBdFvMcVPMcVRsjsBujr9ykL9ykb/Qd1jRd1h2k716lbt7lbzVfFx+l7rVfVyCmrzZgF/ZgWD1e0KFnb7bg2KIoMDuglSLosKMosKRp8boj2rwkVzwklvwkWeXrMmQrtbwlmjwlmvwl2qdsc3wnF7wmXHwm2fwnV3woG+jttHwpWrwpnSpu9SvwNizw9rwtoHwvYvwxZLwyZfwzaH21sbS4vrU4/rW5PrX5fvZ5vvb5/vc6Pve6fvg6vzh7Pzj7fz+59vl7vzm7/zo8P3q8f3r8v3t8/3v9P388+3w9v789O/y9/70+P71+f73+v75+///+/f//Pr//fv///9WDA/EYw43AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfWBhAMAwTysJkTAAAA7UlEQVQY02NgYGCIjgwL8HZzcbCyYoCAqJrqqsqKshKrBpCIo6OdpYm+upKsdLFVA0jEsSFXiodHPLFBuggoABSxzRUqr23kYUuUKLQCAQZrqdK6+kaZYCmRgvy8nGwrBlNOe2d3Uf8ITkEQP8uKQY83PiHWyyuUky/Pxycr05xBUyowLibIz0OKHSifmWHGoJwrFBLu68mfy5Lt45ORbsQg15Cry82lm9vABJRPTzNgkJYUExbg42BlZsz08UlL1QY53RVsPkg+NUUDJOCUl+MDASnJqiABB7h8cpICSMDGwsLM2FBHS01FUV4eAMTGQpNOj1PnAAAAAElFTkSuQmCC",
'dot' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAFfKj/FAAAAA3NCSVQICAjb4U/gAAAA0lBMVEX//////////7X//5n//3j//1r//zzm7/z//x7//wDh7Pzc6PvX5fvW5Prc4vTS4vrJ0uK3yPSzw9qvwNiqvvGvvN+tutCjttGdsc2QrtaTq+aXrMmRp8aJpemLpN2MosKAnuaDnNqFnb58l9h7lbxzlNZ2k71sjsBnitV3ipxiicNggc1YgsVVgMhnfrNffcNYftFVe85ddatSc85JdMxVbaRCbsRNaqdEark6abhAZrQ4Ya4qYrcyW7Y6WpghTJoJS7IoPm0GQpsIPIgIL4sBMngq18WvAAAARnRSTlMA////////////////////////////////////////////////////////////////////////////////////////////iZqVbwAAAAlwSFlzAAAK8AAACvABQqw0mAAAACB0RVh0U29mdHdhcmUATWFjcm9tZWRpYSBGaXJld29ya3MgTVi7kSokAAAA2UlEQVR4nC2OaVvCQAyEJ151N63ihS7KVUKLpXijVRHWrvz/v2RanS/JM0nmDXCDR0JXcGLRp4EAkY2BqxQYkxEsg2XAcoJef/LwatSaTi8GHWbGO6ks6zqsluQYOL28HmoGeqPZ80oMcD6fL72IgKrNumbWZh26Gpqhud4y58AwssxxAaRNTUoNwNnO7t7+waER0+aZf3k1midGR+N7qujt1re2YEZ31VP4+qY6cs4q54V+Pil8EAXrHCtv5b2v6zqELTsXNzgFRjpslfxhU5Esy/OiKMvF4hcq4xjn7deUZgAAAABJRU5ErkJggg==",
'mp3' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAB+1BMVEUgYmc7PkM6Q09CRk1ER01DSE5FSE1GSU5GSk9GSlBHSlBIS09IS1BQVFtQVVtTVltYXWVYXmVYXmZdXl9gYGBeYWZpaWlmbXhsbnFubm5xcXF3d3d5eXlVgMhZgcZYgsV+f4BdhMRehMSBgoNhiMOCg4ViicNlisJni8FpjMGIiIiJiYlsjsBujr+KjI+JjJN8j6lykL9ykb+MjpB2k72OkZd6lbt7lbyQkpWSkpJ+l7qVlZWWlpaCmryFmriFnb6ampqWm6OZm52IoMCLosKfn5+doKWgoKCeoaefoqajo6ORp8ORp8akp6uoqKiXrMmsrKylr7ydsc2zs7OjttGpu9S2ub2yuse9wMOzw9q4w9W4xNa7xdXCxMjFyMvFytHCy9fHy9DAzODJy8/IzNLEzt7F0N/F0OHI0d7H0uPJ0+HK0+HP2OTL2u7N2u7O2uzP2+3O3O/P3O3Q3O3Q3O7Q3O/R3e/S3u7T3u7Y3ejU3+/V3+7W3+3X3+7W4O/Y4O/Z4e7Z4e/T4/nV4/nd5PHZ5fnZ5vra5/vd6Prc6fze6vvf6vrf6/zg6/zg7Pzh7Pvh7Pzi7fzj7fzj7vzj7v3k7vzl7vvl7vzl7v3m7/zm7/3n8P3o8P3p8f3q8vzr8v3s8/3t8/3t9P7u9P3u9f7v9f7w9f7///8AAABpfOEAAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfWCxgAGAUYv3clAAAA80lEQVQY02NgYGCIDA0J8vdxsbeyYoCAwOS42IiwpYutlkNFgv0cbFylFy20Wg4VCXBKSTSWWjAfKAAR8fJdtsRCbN5cKxAACTh4Ll6gJzJ3zswZkyaCBSzd4lVlhGfPmj5lYj9QwNpd2UNBS1J/5tTJEyb0mjFIa0uLV5pKODZMnTShr6fLBKTFu2XmtMbauu6q8o5OQyB/hYFoTj0PO1cWH29mmw6Qz8CUwJ/NFs5SXBQjVKgBErCr4c5gZOEsLROMblYDCThXc6WxpZe05kcJ5CqBBOwrOFM5CtrzWJmTmuRAttiam5sY6WqqqyjKy8oCAAsLR8hfZq/DAAAAAElFTkSuQmCC",
'folder' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABAklEQVR42mNkoBAwAjEXEAcCMTsW+U9AvB6I/+I0gJ2Noe3QJoZKASEgjwkq+h+C795lYPCKZMgC8mZg0fsfbICIIMOcF6cZkrGZ/v8fA0PndIa/7z8BXfAPqgVKv//A8GPeZoYERmE+hjmP1+AwAOgiTjOIP7HZLyjLMJNRmB/ogjMMyYzsUC8wIhQwsgCFWHAHoKAy0AARIYY5r69idwEhIKgBMgAYBq8vkGmALsyAU2QaYAwz4CiZBliADBAAGnCATANsYQbsJtMAJ5ABwGh8uZE8A4R9gAYwMzHkT8hl6Bfgxp5ecIH3Xxj+F05hyIdpUmXAnpnwge9AfJckW7EBAC/gSzisxsnmAAAAAElFTkSuQmCC"
);

return (array_key_exists($ext,$icons)?$icons[$ext]:$icons['file']);
}
