<?php 
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime;
$scriptname = $_SERVER['SCRIPT_NAME']; //eg. /files/dbfiles.php
$basename = basename($_SERVER['PHP_SELF']); //eg. dbfiles.php
$dirname = trim(dirname($_SERVER['PHP_SELF']),"\/"); //eg. files
?> 
<!DOCTYPE html>
<head><title>Find</title>
<style>
table{border: 1px solid black; border-collapse:collapse;}
th, td {border: 1px solid silver;padding:5px;}
th {font-weight:bold;}
ul {list-style-type:none;}
li div {display:inline-block;} 
li div img{vertical-align: middle;float:left;padding:3px;}
img.table {margin:auto;display:block;}
</style>
</head>
<body>
<h1><?php echo ucfirst($dirname); ?></h1>

<?php
set_time_limit(0);
 

require ('../configuration.php');
$config = new JConfig();


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

$count;
echo '<table><thead>';
echo '<th>Table name</th><th>Column</th><th>State</th><th>ID</th><th>Title</th><th>Link</th>';
echo '</thead><tbody>';
foreach ($db_wheres as $key=>$value)
{
    $sql = 'SELECT * FROM '.$key. $value;
    $st = $db->prepare($sql);
    $num = substr_count($sql,'?');
    $params = array_fill(0,$num,'%'.$dirname.'/%');
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row)
    {
      foreach ($row as $k=>$v)
      {
          $regex = '#(href|src)="(.*?)'. $dirname . '\/(.*?)\"#';
          $found = preg_match_all($regex,$v,$matches,PREG_SET_ORDER);
          if ($found)
          {
            $state = (pubstate($row['published']) == 'unknown')? pubstate($row['state']): pubstate($row['published']);
            foreach ($matches as $match)
            {
              $count++;
              echo '<tr><td>'.$key.'</td><td>'.$k.'</td><td>'.$state.'</td><td>'.$row['id'].'</td>';
              echo '<td>'.htmlentities($row['title']).'</td><td>'.filelink($match, $dirname).'</td>'; 
              echo '</tr>';
            }
          } 
      }
     
    }
}
echo '</tbody></table>';
echo 'Links to files found: ' . $count;

function filelink($url,$dirname)
{
  if(file_exists($url[3]))
  {
    return '<a href="'.$url[3].'" target="_blank">'.$url[3].'</a>';
  }
  else
  {
     // try removing %20 and other bad chars from filename in link
     if(file_exists(urldecode($url[3])))
     {
        return '<a href="'.$url[3].'" target="_blank">'.$url[3].'</a>&nbsp;*';
     }
     else
     {
        //return $url[2].$dirname.'/'.$url[3].'<br>'.$url[0];
        return $url[2].$dirname.'/'.$url[3];
     }
  }
}

function pubstate($num)
{
    $ret = '';
    switch ($num)
    {
      case '0' : $ret = 'unpublished'; break;
      case '1' : $ret = 'published'; break;
      case '2' : $ret = 'archived'; break;
      case '-2' : $ret = 'trashed'; break;
      default : $ret = 'unknown';
    }
    return $ret;
} 
                       
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   echo "<p>This page was created in ".round($totaltime,2)." seconds</p>"; 
   echo '<p>&nbsp;</p>';
echo '</body></html>';

