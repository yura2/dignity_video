<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 */

require(getinfo('template_dir') . 'main-start.php');

// доступ к CodeIgniter
$CI = & get_instance();

// загружаем опции
$options = mso_get_option('plugin_dignity_video', 'plugins', array());
if ( !isset($options['limit']) ) $options['limit'] = 10;
if ( !isset($options['slug']) ) $options['slug'] = 'video';

// проверка сегмента
$id = mso_segment(3);
if (!is_numeric($id)) $id = false; // не число
else $id = (int) $id;

if ($id)
{

	// готовим пагинацию
	$pag = array();
	$pag['limit'] = 15;
	$CI->db->from('dignity_video');
	$CI->db->select('dignity_video_id');
	$CI->db->where('dignity_video_comuser_id', $id);
	$CI->db->where('dignity_video_approved', true);
	$query = $CI->db->get();
	$pag_row = $query->num_rows();

	if ($pag_row > 0)
	{
		$pag['maxcount'] = ceil($pag_row / $pag['limit']);

		$current_paged = mso_current_paged();
		if ($current_paged > $pag['maxcount']) $current_paged = $pag['maxcount'];

		$offset = $current_paged * $pag['limit'] - $pag['limit'];
	}
	else
	{
		$pag = false;
	}
	
	// загружаем данные из базы
	$CI->db->from('dignity_video');
	$CI->db->where('dignity_video_comuser_id', $id);
	$CI->db->order_by('dignity_video_datecreate', 'desc');
	$CI->db->join('dignity_video_category', 'dignity_video_category.dignity_video_category_id = dignity_video.dignity_video_category', 'left');
	if ($pag and $offset) $CI->db->limit($pag['limit'], $offset);
	else $CI->db->limit($pag['limit']);	
	$query = $CI->db->get();

	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$allpages = $query->result_array();
		
		// обьявляем переменую	
		$out = '';
		
		// цикл
                foreach ($allpages as $onepage) 
                {
                        $out .= '<div class="page_only">';
			
			$no_approved = '';
			if ($onepage['dignity_video_comuser_id'] == getinfo('comusers_id'))
			{
				if (!$onepage['dignity_video_approved'])
				{
					$no_approved .= '<span style="color:red;">?</span>';
				}
			}
		
                        $out .= '<div class="info info-top"><h1>' . $no_approved;
			
			if($onepage['dignity_video_approved'])
			{
				$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_video_id'] . '">';
			}
			else
			{
				$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_video_id'] . '">';
			}
			
			$out .= $onepage['dignity_video_title'] . '</a> ';
                        
                        $out .= '</h1></div>';
		
                        // если вошел админ
                        if ($onepage['dignity_video_comuser_id'] == getinfo('comusers_id'))
                        {
                                // выводим ссылку «редактировать»
                                $out .= '<p><span style="padding-right:10px;"><img src="' . getinfo('plugins_url') . 'dignity_video/img/edit.png' . '" alt=""></span><a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_video_id'] . '">' . t('Редактировать', __FILE__) . '</a></p>';
                        }
		
                        #$out .= '<p>' . video_cleantext($onepage['dignity_video_text']) . '</p>';
		
			$out .= '<div class="info info-bottom">';
			$out .= '</div>';
			
			$out .= '<div class="break"></div></div><!--div class="page_only"-->';
		
                }
		
		video_menu();
		
		// выводим всё
		echo $out;

		mso_hook('pagination', $pag);

	}
	else
	{ 
                video_menu();
		video_not_found();
	}
}
else
{
	video_menu();
	video_not_found();
}

require(getinfo('template_dir') . 'main-end.php');

// конец файла
