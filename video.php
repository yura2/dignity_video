<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 */

// начало шаблона
require(getinfo('template_dir') . 'main-start.php');

// доступ к CI
$CI = & get_instance();

// загружаем опции
$options = mso_get_option('plugin_dignity_video', 'plugins', array());
if ( !isset($options['limit']) ) $options['limit'] = 10;
if ( !isset($options['slug']) ) $options['slug'] = 'video';

// готовим пагинацию
$pag = array();
$pag['limit'] = $options['limit'];
$CI->db->select('dignity_video_id');
$CI->db->from('dignity_video');
$CI->db->where('dignity_video_approved', true);
$CI->db->where('dignity_video_ontop', true);
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

// берём видео из базы
$CI->db->from('dignity_video');
$CI->db->where('dignity_video_approved', true);
$CI->db->where('dignity_video_ontop', true);
$CI->db->join('dignity_video_category', 'dignity_video_category.dignity_video_category_id = dignity_video.dignity_video_category', 'left');
$CI->db->join('comusers', 'comusers.comusers_id = dignity_video.dignity_video_comuser_id', 'left');
$CI->db->order_by('dignity_video_datecreate', 'desc');
if ($pag and $offset) $CI->db->limit($pag['limit'], $offset);
else $CI->db->limit($pag['limit']);
$query = $CI->db->get();

// если есть что выводить...
if ($query->num_rows() > 0)	
{
	$allpages = $query->result_array();
	
	$out = '';
	
	// путь к картинкам
	$path = getinfo('plugins_url') . 'dignity_video/img/';
	
	// выводим иконку rss
	$out .= '<div class="right"><a href="' . getinfo('site_url') . $options['slug'] . '/rss/' . '"><img src="' . $path . 'rss.gif' . '"></a></div>';
	
	foreach ($allpages as $onepage) 
	{
		$out .= '<div class="page_only">';
		
		$out .= '<div class="info info-top">';
		$out .= '<h1><a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_video_id'] . '">' . $onepage['dignity_video_title'] . '</a></h1>';
		// если вошел админ
                if ($onepage['dignity_video_comuser_id'] == getinfo('comusers_id'))
                {
                	// выводим ссылку «редактировать»
                        $out .= '<p><span style="padding-right:10px;"><img src="' . getinfo('plugins_url') . 'dignity_video/img/edit.png' . '" alt=""></span><a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_video_id'] . '">' . t('Редактировать', __FILE__) . '</a></p>';
                }
		$out .= '</div>';
		
		$out .= '<p>' . video_cleantext($onepage['dignity_video_text']) . '</p>';
		
		$out .= '<div class="info info-bottom">';
		$out .= '<p style="text-align:right;">';

		$out .= '<span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_video/img/user.png' . '"></span> <a href="' . getinfo('site_url') . $options['slug'] . '/all_one_author/' . $onepage['dignity_video_comuser_id'] . '">' . $onepage['comusers_nik'] . '</a>';
		
		$out .= ' | <span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_video/img/public.png' . '"></span>' . mso_date_convert($format = 'd.m.Y, H:i', $onepage['dignity_video_datecreate']);
			
		$out .= ' | <span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_video/img/views.png' . t('Просмотров: ', __FILE__) . $onepage['dignity_video_views'];
			
		if ($onepage['dignity_video_category_id'])
		{
			$out .= ' | <span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_video/img/ordner.png' . '"></span>' . t('Рубрика:', __FILE__) . ' <a href="' . getinfo('site_url') . $options['slug'] . '/category/' . $onepage['dignity_video_category_id'] . '">' . $onepage['dignity_video_category_name'] . '</a>';
		}
		else
		{
			$out .= ' | ' . t('Рубрика:', __FILE__)  . ' <a href="' . getinfo('site_url') . $options['slug'] .'">' . t('Все видео', __FILE__) . '</a>';	
		}

		$CI->db->from('dignity_video_comments');
		$CI->db->where('dignity_video_comments_approved', true);
		$CI->db->where('dignity_video_comments_thema_id', $onepage['dignity_video_id']);
		$out .= ' | <span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_video/img/comments.png' . '"></span>' . t('Комментарий: ', __FILE__) . $CI->db->count_all_results();
		
		$out .= '</p>';
		$out .= '</div>';
			
		$out .= '<div class="break"></div></div><!--div class="page_only"-->';
		
	}
	
	// выводим навигацию видео
	video_menu();
	
	// выводим всё
	echo $out;
	
	// добавляем пагинацию
	mso_hook('pagination', $pag);
}
else
{
	// выводим навигацию видео
	video_menu();
	
	echo t('Видео нет.', __FILE__);
}

require(getinfo('template_dir') . 'main-end.php');

# end of file
