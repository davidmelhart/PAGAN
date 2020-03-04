<?php
# Scripts HTML block
# parameters:
#	scripts - array of strings; additional script paths, defaults to None
echo
	'<script src="static/js/jquery.min.js"></script>
	 <script src="static/js/tooltip.js"></script>
	';
if(isset($scripts)){
	foreach ($scripts as &$script) {
		echo
			'<script src="static/js/'.$script.'"></script>';
	}
}