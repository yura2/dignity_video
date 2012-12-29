<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/*
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 * https://github.com/dignityinside/dignity_video (github)
 * License GNU GPL 2+
 */

echo '<h1>' . t('Видео', __FILE__) . '</h1>';

$CI = & get_instance();

$options_key = 'plugin_dignity_video';
	
if ( $post = mso_check_post(array('f_session_id', 'f_submit')) )
{
	mso_checkreferer();
	
	$options = array();
	$options['header'] = $post['f_header'];
	$options['slug'] = $post['f_slug'];
	$options['limit'] = $post['f_limit'];
	$options['ontop'] = isset($post['f_ontop']) ? 1 : 0;
	$options['cackle_code'] = $post['f_cackle_code'];
	
	mso_add_option($options_key, $options, 'plugins');
	echo '<div class="update">' . t('Обновлено!', 'plugins') . '</div>';
}

$options = mso_get_option($options_key, 'plugins', array());
if ( !isset($options['header']) ) $options['header'] = 'Видео'; 
if ( !isset($options['slug']) ) $options['slug'] = 'video'; 
if ( !isset($options['limit']) ) $options['limit'] = 10;
if ( !isset($options['cackle_code']) ) $options['cackle_code'] = '';

echo '<h2>' . t('Настройки', __FILE__) . '</h2>';

$form = '';
$form .= '<form action="" method="post">' . mso_form_session('f_session_id');
$form .= '<p>' . t('Заголовок страницы:', 'plugins') . ' ' . ' <input name="f_header" type="text" value="' . $options['header'] . '" style="width:50%"></p>';
$form .= '<p>' . t('Коротка ссылка:', 'plugins') . ' ' . ' <input name="f_slug" type="text" value="' . $options['slug'] . '"></p>';
$form .= '<p>' . t('Записей на страницу:', 'plugins') . ' ' . ' <input name="f_limit" type="text" value="' . $options['limit'] . '"></p>';
$form .= '<p>' . t('Cackle код:', __FILE__) . '<br>'
    . '<textarea name="f_cackle_code" cols="90" rows="5">' . $options['cackle_code'] . '</textarea></p>';

// сразу на главную?
$chckout = ''; 
if (!isset($options['ontop']))  $options['ontop'] = true;
if ( (bool)$options['ontop'] )
{
	$chckout = 'checked="true"';
} 
$form .= '<p>' . t('Сразу на главную?', __FILE__)
	. ' <input name="f_ontop" type="checkbox" ' . $chckout . '></p>';

$form .= '<input type="submit" name="f_submit" value="' . t('Сохранить', 'plugins') . '" style="margin: 25px 0 5px 0;">';
$form .= '</form>';

echo $form;

###########################

// если был post
if ($post = mso_check_post(array('f_session_id', 'f_submit_dignity_video_category')) )
{
	// проверяем реферала
	mso_checkreferer();	

	// готовим массив для добавления в базу данных
	$ins_data = array (
			'dignity_video_category_name' => $post['f_dignity_video_category_name'],
			'dignity_video_category_description' => $post['f_dignity_video_category_description'],
			'dignity_video_category_position' => $post['f_dignity_video_category_position']
			);
	
	// добавляем в базу
	$res = ($CI->db->insert('dignity_video_category', $ins_data)) ? '1' : '0';

	// результат
	if ($res)
	{
		// если всё окей
		echo '<div class="update">' . t('Категория добавлена!', __FILE__) . '</div>';	
	}
	// если ошибки
	else echo '<div class="error">' . t('Ошибка добавления в базу данных...', __FILE__) . '</div>';
		
	// сбрасываем кеш
	mso_flush_cache();
}		

$form = '';
$form .= '<h2>' . t('Категории', __FILE__) . '</h2>';
$form .= '<form action="" method="post">' . mso_form_session('f_session_id');

$form .= '<p><strong>' . t('Название:', __FILE__) . '</strong><span style="color:red;">*</span><br>
	<input name="f_dignity_video_category_name" type="text" style="width:50%" required="required"></p>';
	
$form .= '<p><strong>' . t('Описание:', __FILE__) . '</strong><br>
	<input name="f_dignity_video_category_description" type="text" style="width:50%"></p>';
	
$form .= '<p><strong>' . t('Порядок:', __FILE__) . '</strong><br>
	<input name="f_dignity_video_category_position" type="text" style="width:50%"></p>';

$form .= '<p><input type="submit" class="submit" name="f_submit_dignity_video_category" value="' . t('Добавить', __FILE__) . '">';
$form .= '</form>';	

// выводим форму
echo $form;

// загружаем библиотеку table
$CI->load->library('table');

// массив с таблицей
$tmpl = array (
		'table_open' => '<br><table class="page" border="0" width="100%">',
		'row_alt_start' => '<tr class="alt">',
		'cell_alt_start' => '<td class="alt" style="vertical-align: top;">',
		'cell_start' => '<td style="vertical-align: top;">',
		);

// создаём табилицу
$CI->table->set_template($tmpl);

// заголовки таблицы
$CI->table->set_heading('id', t('Информация', __FILE__));

// выводим данные из базы
$CI->db->from('dignity_video_category');
$CI->db->order_by('dignity_video_category_position', 'desc');
$query = $CI->db->get();

if ($query->num_rows() > 0)	
{	
	$categorys = $query->result_array();
	
	foreach ($categorys as $rw) 
	{
		$id = $rw['dignity_video_category_id'];
		
		# удаление
		if ( $post = mso_check_post(array('f_session_id', 'f_submit_dignity_video_category_delete')) )
		{
			mso_checkreferer();
			
			if ( !isset($post['f_id'])) $post['f_id'] = $id;
			
			$CI->db->where('dignity_video_category_id', $post['f_id']);
			$CI->db->delete('dignity_video_category');
			
			mso_flush_cache();
			
			echo '<div class="update">' . t('Удалено!', __FILE__) . '<script>location.replace(window.location); </script></div>';
			
			return;
		}
		
		# редактирование
		if ( $post = mso_check_post(array('f_session_id', 'f_submit_dignity_video_category_edit')) )
		{
			mso_checkreferer();
			
			if ( !isset($post['f_id'])) $post['f_id'] = $id;
			
			$data = array (
					'dignity_video_category_name' => htmlspecialchars($post['f_dignity_video_category_name']),
					'dignity_video_category_description' => htmlspecialchars($post['f_dignity_video_category_description']),
					'dignity_video_category_position' => htmlspecialchars($post['f_dignity_video_category_position']),
			);
			
			$CI->db->where('dignity_video_category_id', $post['f_id']);
			
			if ($CI->db->update('dignity_video_category', $data ) )
				echo '<div class="update">' . t('Обновлено!', __FILE__) . '<script>location.replace(window.location); </script></div>';
			else 
				echo '<div class="error">' . t('Ошибка обновления', __FILE__) . '</div>';
				
			mso_flush_cache();
			
			return;
		}
		
		$form = '';
		$form .= '<form action="" method="post">' . mso_form_session('f_session_id');
		$form .= '<p>' . t('Название:', __FILE__) . '<br> <input name="f_dignity_video_category_name" type="text" style="width:90%" value="' . $rw['dignity_video_category_name'] . '"></p>';
		$form .= '<p>' . t('Описание:', __FILE__) . '<br> <input name="f_dignity_video_category_description" type="text" style="width:90%" value="' . $rw['dignity_video_category_description'] . '"></p>';
		$form .= '<p>' . t('Позиция:', __FILE__) . '<br> <input name="f_dignity_video_category_position" type="text" style="width:90%" value="' . $rw['dignity_video_category_position'] . '"></p>';
		$form .= '<input type="hidden" name="f_id" value="' . $id . '" />';
		$form .= '<input type="submit" name="f_submit_dignity_video_category_edit" value="' . t('Сохранить', __FILE__) . '" style="margin: 10px 0;">';
		$form .= ' <input type="submit" name="f_submit_dignity_video_category_delete" onClick="if(confirm(\'' . t('Удалить?', __FILE__) . ' ' . t('Заявку №', __FILE__) . $rw['dignity_video_category_id'] . '\')) {return true;} else {return false;}" value="' . t('Удалить', __FILE__) . '">';
		$form .= '</form>';
		
		$CI->table->add_row(
				$id,
				$form
				);
	}
	
	// генерируем таблицу
	echo $CI->table->generate();
}
// выводим сообщения
else echo t('Категорий нет.', __FILE__);

#конец файла
