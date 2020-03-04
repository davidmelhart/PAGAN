<?php
# Footer HTML block
# parameters:
#	tooltip - HTML; tooltip associated with the page
echo
	'
	</div>
		<div id="tooltip">
			<div class="container">
				<div class="close-container">
					<div class="icon close">x</div>
				</div>
				<div class="inner">';
				if(isset($tooltip)){
					echo $tooltip;
				}
echo			'</div>
			</div>
		</div>
	</BODY>
	</HTML>';
?>