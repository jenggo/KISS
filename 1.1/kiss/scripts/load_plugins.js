/*<![CDATA[*/
	$(document).ready(function() {
		$("#pilih").click(function() {
            var checked_status = this.checked;
            $(".pilihan").each(function() {
              this.checked = checked_status;
            });
        });
		$('#isi').sMarkUp('html', 300);
		var remove = document.getElementById('remove');
		remove.onclick = function() {
			if (this.rel == 'on') {
				this.innerHTML = '+ SmartMarkUP';
				this.rel = 'off';
				$.sMarkUpRemove('#isi');
			}
			else {
				this.innerHTML = '- SmartMarkUP';
				this.rel = 'on';
				$('#isi').sMarkUp('html', 300);
			}
			return false;
		};
	});
/*]]>*/