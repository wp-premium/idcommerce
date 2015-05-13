<li class="myprojects author-<?php echo $post->post_author; ?>" data-author="<?php echo $post->post_author; ?>">
	<div title="Project Thumbnail" class="project-item project-thumb"><div class="image" style="<?php echo (!empty($thumb) ? 'background-image: url('.$thumb.');' : ''); ?>"></div></div>
	<div title="Project Name" class="project-item project-name"><?php echo get_the_title($post_id); ?></div>
	<div class="project-item option-list">
		<?php 
		$actions = '<a title="Edit Project" href="'.md_get_durl().$prefix.'edit_project='.$post_id.'"><i class="fa fa-edit"></i></a>';
		$actions .= '<a title="Upload File" href="'.md_get_durl().$prefix.'project_files='.$post_id.'"><i class="fa fa-cloud-upload"></i></a>';
		$actions .= '<a title="View Project" href="'.$permalink.'"><i class="fa fa-eye"></i></a>';
		$actions .= '<a title="Export Orders" href="'.md_get_durl().$prefix.'export_project='.$post_id.'"><i class="fa fa-file-excel-o"></i></a>';
		echo apply_filters('id_myprojects_actions', $actions, $post, $user_id);
		?>
	</div>
	<div title="Project Status" class="project-item project-status"><?php echo (strtoupper($status) == 'PUBLISH' ? __('PUBLISHED', 'memberdeck') : $status); ?></div>
</li>