<?php 
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 
;?> 
<!DOCTYPE html>
<head><title>Find</title>
<style>
table{border: 1px solid black; border-collapse:collapse;}
th, td {border: 1px solid silver;padding:5px;}
</style>
</head>
<body>
<h1>Images</h1>

<?php
set_time_limit(0);


function f_images($a)
{
  return preg_match('#(jpg|JPG|gif|GIF|png|png|jpeg|JPEG)$#',$a);
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

echo '<p><strong>'.($dir == $currdir?'images':'images/'.$dir).'</strong></p>';
// now set $dir to something useful for links
$dir = ($dir == $currdir?'':$dir . '/');

// filter out unwanted files
$files = array_filter($foundfiles,'f_images');

$folders = preg_grep('#\.#',$foundfiles,PREG_GREP_INVERT);
$folders = array_filter($folders,'f_folders');

$prefix = $config->dbprefix;
// reduce the number of tables to search
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
$excluded_images = array('fake.gif');
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
        $params = array_fill(0,$num,'%'.$dir.$file.'%');
        $st->execute($params);
        $rows = $st->fetchColumn();
        //$rows = $st->fetchAll(PDO::FETCH_ASSOC);
        
        if($rows > 0)
        {
          $found[$file] = '<tr><td><a href="'.$dir.$file.'" target="_blank">'.$file.'</a></td><td>'.str_replace($prefix,'',$key).'</td><td>'.$rows.'</td></tr>';
        }

        unset($params);
    }
}
$files = array_flip($files);
echo '<table><tbody>';
echo implode($found);
echo '</tbody></table><h2>Images not found in db</h2><ul>';
$notfound = array_diff_key($files,$found);
foreach($notfound as $file=>$value)
{
  echo '<li><a href="'.$dir.$file.'" target="_blank">'.$file.'</a></li>';
}
echo '</ul><h2>Folders</h2>';

echo '<ul>';
foreach ($folders as $folder){
echo '<li><a href="dbimages.php?dir='.$dir.$folder.'">'.$folder.'</a></li>';
}
echo '</ul>';
                                            
echo '</body></html>';
 
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "This page was created in ".round($totaltime,2)." seconds"; 

