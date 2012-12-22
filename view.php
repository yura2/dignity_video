<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 */

// загружаем начало шаблона
require(getinfo('template_dir') . 'main-start.php');

// загружаем опции
$options = mso_get_option('plugin_dignity_video', 'plugins', array());
if ( !isset($options['cackle_code']) ) $options['cackle_code'] = '';

// получаем доступ к CI
$CI = & get_instance();

// проверка сегмента
$id = mso_segment(3);
if (!is_numeric($id)) $id = false;
else $id = (int) $id;

// если число
if ($id)
{
	// загружаем данные из базы
	$CI->db->from('dignity_video');
	$CI->db->where('dignity_video_id', $id);
	$CI->db->join('dignity_video_category', 'dignity_video_category.dignity_video_category_id = dignity_video.dignity_video_category', 'left');
	$CI->db->join('comusers', 'comusers.comusers_id = dignity_video.dignity_video_comuser_id', 'left');
	if (!is_login())
	{
		$CI->db->where('dignity_video_approved', 1);
	}
	$query = $CI->db->get();

	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$allpages = $query->result_array();
		
		// обьявляем переменую	
		$out = '';
		
		// возвращаем туда, откуда пришел
		$url = getinfo('site_url') . $options['slug'];
		
		// цикл
		foreach ($allpages as $onepage) 
		{
			
			// подсчёт количества просмотров через cookie
			global $_COOKIE;
			$name_cookies = 'dignity-video';
			$expire = 2592000;
			$slug = getinfo('siteurl') . $options['slug'] . '/' . mso_segment(2) . '/' . mso_segment(3);
			$all_slug = array();
			
			if (isset($_COOKIE[$name_cookies]))
			{
				$all_slug = explode('|', $_COOKIE[$name_cookies]); // значения текущего кука
			}
			
			if (in_array($slug, $all_slug))
			{
				false; // уже есть текущий урл - не увеличиваем счетчик
			}
			else
			{
				// нужно увеличить счетчик
				$all_slug[] = $slug; // добавляем текущий slug
				$all_slug = array_unique($all_slug); // удалим дубли на всякий пожарный
				$all_slug = implode('|', $all_slug); // соединяем обратно в строку
				$expire = time() + $expire;
				
				@setcookie($name_cookies, $all_slug, $expire);
				
				$page_view_count = $onepage['dignity_video_views'] + 1;
				
				$CI->db->where('dignity_video_id', $id);
				$CI->db->update('dignity_video', array('dignity_video_views'=>$page_view_count));
			}
			
		$out .= '<div class="page_only">';
		
		$out .= '<div class="info info-top">';
		$out .= '<h1><a href="' . $url . '">' . $onepage['dignity_video_title'] . '</a></h1>';
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

		$out .= '</p>';

		$out .= '<p style="font-weight:bold;">' . t('Получить ссылку на загруку видео:', __FILE__) . '</p>';
		$out .= "<p><form action=\"http://savefrom.net/index.php\" method=\"get\" target=\"_blank\">
<input type=\"text\" name=\"url\" value=\"Введите сюда ссылку на видео\" size=\"32\" style=\"color:#f07000; margin-right:5px;\" onfocus=\"this.value=''; this.onfocus=null;\" /><input type=\"submit\" value=\"Скачать\" style=\"width:70px;\" /></form></p>";
		$out .= '<p style="font-weight:bold;">' . t('Поделиться:', __FILE__) . '</p>';
		$out .= '<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>';
		$out .= '<div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="icon" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki"></div>';

		$out .= '</div>';
		$out .= '<div class="break"></div>';
		$out .= '</div><!--div class="page_only"-->';
		
		}
		
		video_menu();
		
		echo $out;
		
		// meta-тэги
		mso_head_meta('title', $onepage['dignity_video_title']);
		mso_head_meta('description', $onepage['dignity_video_keywords']);
		mso_head_meta('keywords', $onepage['dignity_video_description']);
		
		#############################################################
		
		# Комментарии
		
		// готовим пагинацию
		$pag = array();
		$pag['limit'] = 10;
		$CI->db->select('dignity_video_comments_id');
		$CI->db->from('dignity_video_comments');
		$CI->db->where('dignity_video_comments_approved', true);
		$CI->db->where('dignity_video_comments_thema_id', $id);
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

		$CI->db->from('dignity_video_comments');
		$CI->db->where('dignity_video_comments_approved', true);
		$CI->db->where('dignity_video_comments_thema_id', $id);
		$CI->db->order_by('dignity_video_comments_datecreate', 'asc');
		$CI->db->join('comusers', 'comusers.comusers_id = dignity_video_comments.dignity_video_comments_comuser_id', 'left');
		if ($pag and $offset) $CI->db->limit($pag['limit'], $offset);
		else $CI->db->limit($pag['limit']);
		$query = $CI->db->get();

		// если есть что выводить...
		if ($query->num_rows() > 0)	
		{
			$allcomments = $query->result_array();
	
			// обьявляем переменую
			$comments_out = '';
			
			$comments_out .= '<div class="leave_a_comment">Комментарии через наш сайт:</div>';
			
			$comments_out .= '<ol>';
	
			// цикл
			foreach ($allcomments as $onecomment) 
			{
				
				// если админ
				if (is_login())
				{
			
					# удаление
					if ( $post = mso_check_post(array('f_session_id', 'f_submit_dignity_video_comments_delete')) )
					{
						mso_checkreferer();
				
						if ( !isset($post['f_id'])) $post['f_id'] = $onecomment['dignity_video_comments_id'];
				
						$CI->db->where('dignity_video_comments_id', $post['f_comments_id']);
						$CI->db->delete('dignity_video_comments');
					
						mso_flush_cache();
				
						$comments_out .= '<div class="update">' . t('Удалено!', __FILE__) . '<script>location.replace(window.location); </script></div>';
		
					}
		
					$form = '';
					$form .= '<form action="" method="post">' . mso_form_session('f_session_id');
					$form .= '<input type="hidden" name="f_comments_id" value="' . $onecomment['dignity_video_comments_id'] . '" />';
					$form .= '<p class="right"><input type="submit" name="f_submit_dignity_video_comments_delete" onClick="if(confirm(\'' . t('Удалить?', __FILE__) . ' ' . t('Комментарий №', __FILE__) . $onecomment['dignity_video_comments_id'] . '\')) {return true;} else {return false;}" value="' . t('x', __FILE__) . '"></p>';
					$form .= '</form>';
			
					$comments_out .= $form;
				}
				
				$comments_out .= '<div class="type type_page_comments">
				<div class="comments">
				<li style="clear: both" class="users">
					<div class="comment-info">
					<span class="date">' .
					'Комментарий от ' . $onecomment['comusers_nik'] . ' в ' . mso_date_convert($format = 'H:i → d.m.Y', $onecomment['dignity_video_comments_datecreate']) . '</span></div>
					<div class="comments_content"><p>' . $onecomment['dignity_video_comments_text'] . '</p></div>
				</li></div>
				<div class="break"></div>
				</div><!-- class="type type_page_comments" -->';
		
			}
			
			$comments_out .= '</ol>';
			
			
			echo $comments_out;
	
			// добавляем пагинацию
			mso_hook('pagination', $pag);
		}
		else
		{

			echo '<div class="leave_a_comment">Комментарии через наш сайт:</div>';			

			echo '<p>' . t('Нет комментариев. Ваш будет первым!', __FILE__) . '</p>';
			
			if (!is_login_comuser())
			{
				echo '<a href="' . getinfo('site_url') . 'login' . '">' . t('Войти', __FILE__) . '</a>';
				echo ' | ';
				echo '<a href="' . getinfo('site_url') . 'register' . '">' . t('Регистрация', __FILE__) . '</a>';

				if ($options['cackle_code'])
				{
					echo '<div class="leave_a_comment">Комментарии через социальные сети:</div>';
					echo $options['cackle_code'];
				}

			}
		}
		
		 // если комюзер
		if (is_login_comuser() && $onepage['dignity_video_comments'])
		 {
            
			// если пост
			if ( $post = mso_check_post(array('f_session_id', 'f_submit_dignity_video_comments_add')) )
			{
				// id == 3 сегмент
				$id = mso_segment(3);
                        
				// проверяем реферала
				mso_checkreferer();
                        
				// готовим массив
				$ins_data = array (
				        'dignity_video_comments_text' => htmlspecialchars($post['f_dignity_video_comments_text']),
				        'dignity_video_comments_datecreate' => date('Y-m-d H:i:s'),
				        'dignity_video_comments_dateupdate' => date('Y-m-d H:i:s'),
				        'dignity_video_comments_thema_id' => $id,
				        'dignity_video_comments_approved' => 1,
				        'dignity_video_comments_comuser_id' => getinfo('comusers_id'),
				);
				
				// результат...
				$res = ($CI->db->insert('dignity_video_comments', $ins_data)) ? '1' : '0';
                        
				if ($res)
				{
				         // всё окей
					echo '<div class="update">' . t('Комментарий добавлен!', __FILE__) . '</div>';
					echo '<script>location.replace(window.location); </script>';
				}
				// если ошибка
				else echo '<div class="error">' . t('Ошибка добавления в базу данных...', __FILE__) . '</div>';
		
				 // Сбрасываем кеш
				mso_flush_cache();
                        
			 }
			else
			{
			        // начало формы  
			        $form = '';     
				$form .= '<h2>' . t('Оставьте комментарий!', __FILE__) . '</h2>';     
				$form .= '<form action="" method="post">' . mso_form_session('f_session_id');
				
				$form .= '<p><strong>' . t('Текст (можно использовать bb-code):', __FILE__) . '<span style="color:red;">*</span></strong><br><textarea name="f_dignity_video_comments_text" class="markItUp"
					cols="80" rows="10" value="" required="required" style="margin-top: 2px; margin-bottom: 2px; "></textarea>';
			
				// конец формы
				$form .= '<p><input type="submit" class="submit" name="f_submit_dignity_video_comments_add" value="' . t('Отправить', __FILE__) . '"></p>';
				$form .= '</form>';
                        
				// выводим форму
				echo $form;
			}

			if ($options['cackle_code'])
			{
				echo '<div class="leave_a_comment">Комментарии через социальные сети:</div>';
				echo $options['cackle_code'];
			}

		}
		
	}
	else
	{
		// если запись не найдена
		video_menu();
		video_not_found();
	}
}
else
{
	// если не номер
	video_menu();
	video_not_found();
}

require(getinfo('template_dir') . 'main-end.php');

// конец файла
