$(document).ready(function() {
	$.fn.editable.defaults.mode = 'inline';
	$.fn.editable.defaults.showbuttons = false;
	$.fn.editable.defaults.onblur = 'submit';
	$.fn.editabletypes.textarea.defaults.rows = 4;

	function applyEditable() {
		$('.cat-add').editable({
			emptytext: 'Enter info...'
		});

		$('.qbank-tadd').editable();

		$('.cat-edit').editable({
			ajaxOptions: {
				dataType: 'json'
			},
			url: 'api?action=updateCat',
			success: function(resp, value) {
				return resp.onerror == undefined ? 'General Error' : (resp.onerror ? resp.msg : null);
			},
			error: function() {
				return 'General Error';
			},
			timeout: function() {
				return 'Timed out';
			}
		});

		$('.qbank-edit').editable({
			ajaxOptions: {
				dataType: 'json'
			},
			emptytext: '( Empty )',
			url: 'api?action=updateQuestion',
			success: function(resp, value) {
				return resp.onerror == undefined ? 'General Error' : (resp.onerror ? resp.msg : null);
			},
			error: function() {
				return 'General Error';
			},
			timeout: function() {
				return 'Timed out';
			}
		});
	}
	
	applyEditable();

	$('.cat-manage').on('focus', function(e) {
		e.preventDefault();
		$(this).parent().parent().find('a[data-name="cat-info"]').editable('submit');
	})

	$('.qbank-ans').on('focus', function(e) {
		e.preventDefault();
		$(this).parent().parent().find('a[data-name="qbank-feedback"]').editable('submit');
		$(this).click();
	})

	$('#modal-edit-submit').on('click', function(e) {
		e.preventDefault();

		var qid = $(this).attr('data-qid');

		$.ajax({
			type: 'POST',
			url: 'api?action=updateAnswers',
			data: {
				'qid': qid,
				'ans0-text':        $('#edit-ques-ans-0-text').val(),
				'ans0-correct':     $('#edit-ques-ans-0-correct').prop('checked'),
				'ans0-aid':         $('#edit-ques-ans-0-text').attr('data-aid'),
				'ans1-text':        $('#edit-ques-ans-1-text').val(),
				'ans1-correct':     $('#edit-ques-ans-1-correct').prop('checked'),
				'ans1-aid':         $('#edit-ques-ans-1-text').attr('data-aid'),
				'ans2-text':        $('#edit-ques-ans-2-text').val(),
				'ans2-correct':     $('#edit-ques-ans-2-correct').prop('checked'),
				'ans2-aid':         $('#edit-ques-ans-2-text').attr('data-aid'),
				'ans3-text':        $('#edit-ques-ans-3-text').val(),
				'ans3-correct':     $('#edit-ques-ans-3-correct').prop('checked'),
				'ans3-aid':         $('#edit-ques-ans-3-text').attr('data-aid')
			},
			dataType: 'json',
			success: function(resp) {
				if (resp.onerror == undefined || resp.onerror) {
					$('#edit-ques-ans-0-text').focus();
					$('#edit-ques-ans-alert-context').addClass('alert-danger');
					$('#edit-ques-ans-alert-content').html(resp.msg == undefined ? 'General Error' : resp.msg);
					$('#edit-ques-ans-alert').fadeIn('fast');
					
					setTimeout(function() {
						$('#edit-ques-ans-alert').fadeOut(function() {
							$('#edit-ques-ans-alert-context').removeClass('alert-danger');
							$('#edit-ques-ans-alert-content').html('');
						})
					}, 2500);
				} else {
					$('#modal-edit').modal('hide');
				}
			},
			error: function() {
				$('#edit-ques-ans-0-text').focus();
				$('#edit-ques-ans-alert-context').addClass('alert-danger');
				$('#edit-ques-ans-alert-content').html('Request error. Please check your fields and try again.');
				$('#edit-ques-ans-alert').fadeIn('fast');

				setTimeout(function() {
					$('#edit-ques-ans-alert').fadeOut(function() {
						$('#edit-ques-ans-alert-context').removeClass('alert-danger');
						$('#edit-ques-ans-alert-content').html('');
					})
				}, 2500);
			},
			timeout: function() {
				$('#edit-ques-ans-0-text').focus();
				$('#edit-ques-ans-alert-context').addClass('alert-danger');
				$('#edit-ques-ans-alert-content').html('Timed out, please check your network connection.');
				$('#edit-ques-ans-alert').fadeIn('fast');

				setTimeout(function() {
					$('#edit-ques-ans-alert').fadeOut(function() {
						$('#edit-ques-ans-alert-context').removeClass('alert-danger');
						$('#edit-ques-ans-alert-content').html('');
					})
				}, 2500);
			}
		})
	})

	$('#add-ques-submit').on('click', function(e) {
		e.preventDefault();

		$.ajax({
			type: 'POST',
			url: 'api?action=addQuestion',
			data: {
				'cat-id':           $('#cat-id').val(),
				'ques-name':        $('#add-ques-name').val(),
				'ques-text':        $('#add-ques-text').val(),
				'ques-fback':       $('#add-ques-feedback').val(),
				'ques-beep':        $('#add-ques-beep').prop('checked'),
				'ans0-text':        $('#add-ques-ans-0-text').val(),
				'ans0-correct':     $('#add-ques-ans-0-correct').prop('checked'),
				'ans1-text':        $('#add-ques-ans-1-text').val(),
				'ans1-correct':     $('#add-ques-ans-1-correct').prop('checked'),
				'ans2-text':        $('#add-ques-ans-2-text').val(),
				'ans2-correct':     $('#add-ques-ans-2-correct').prop('checked'),
				'ans3-text':        $('#add-ques-ans-3-text').val(),
				'ans3-correct':     $('#add-ques-ans-3-correct').prop('checked')
			},
			dataType: 'json',
			success: function(resp) {
				if (resp.onerror == undefined || resp.onerror) {
					$('#add-ques-name').focus();
					$('#add-ques-alert-context').addClass('alert-danger');
					$('#add-ques-alert-content').html(resp.msg == undefined ? 'General Error' : resp.msg);
					$('#add-ques-alert').fadeIn('fast');
					setTimeout(function() {
						$('#add-ques-alert').fadeOut(function() {
							$('#add-ques-alert-context').removeClass('alert-danger');
							$('#add-ques-alert-content').html('');
						})
					}, 2500);
				} else {
					if ($('#no_question').val() == '1') {
						window.location.reload();
						return;
					}

					$(
						'<tr id="question-row-' + resp.data.id + '">' +
						'    <td style="vertical-align: middle"><input class="qbank-bulk" type="checkbox" data-pk="' + resp.data.id + '" tabindex=-1> &nbsp;' + resp.data.id + '</td>' +
						'    <td><a href="#" class="qbank-edit editable-item" data-name="qbank-name" data-type="textarea" data-pk="' + resp.data.id + '" data-title="Question name">' + $('#add-ques-name').val() + '</a></td>' +
						'    <td><a href="#" class="qbank-edit editable-item" data-name="qbank-info" data-type="textarea" data-pk="' + resp.data.id + '" data-title="Question Text">' + $('#add-ques-text').val() + '</a></td>' +
						'    <td><a href="#" class="qbank-edit editable-item" data-name="qbank-correctans" datadata-type="textarea" data-pk="' + resp.data.id + '" data-title="Correct Answer">' + $('#add-ques-ans-0-text').val() + '</a></td>' +
						'    <td>' + ($('#add-ques-beep').prop('checked') ? '<i class="fa fa-volume-up" aria-hidden="true"></i> ' : '') + '<a href="#" class="qbank-edit editable-item" data-name="qbank-feedback" data-type="textarea" data-pk="' + resp.data.id + '" data-title="General Feedback">' + $('#add-ques-feedback').val() + '</a></td>' +
						'    <td><a href="#" data-pk="' + resp.data.id + '" data-keyboard="true" class="qbank-edit-modal btn btn-info btn-xs"><i class="fa fa-paint-brush" aria-hidden="true"></i> Answer</a></td>' +
						'</tr>'
					).insertBefore($('#question-tadd-general'));

					$('textarea[class*="add-ques"]').html('');
					$('textarea[class*="add-ques"]').val('');
					$('input[class*="add-ques"][type="checkbox"]').prop('checked', false);

					$('td[class="qbank-edit"][data-pk="' + resp.data.id + '"]').editable('activate');
					$('.qbank-edit[data-pk="' + resp.data.id + '"]').css('border-bottom', 'dashed 1px #0088cc');

					applyEditable();

					$('#add-ques-name').focus();
				}
			},
			error: function() {
				$('#add-ques-name').focus();
				$('#add-ques-alert-context').addClass('alert-danger');
				$('#add-ques-alert-content').html('Request error. Please check your fields and try again.');
				$('#add-ques-alert').fadeIn('fast');

				setTimeout(function() {
					$('#add-ques-alert').fadeOut(function() {
						$('#add-ques-alert-context').removeClass('alert-danger');
						$('#add-ques-alert-content').html('');
					})
				}, 2500);
			},
			timeout: function() {
				$('#add-ques-name').focus();
				$('#add-ques-alert-context').addClass('alert-danger');
				$('#add-ques-alert-content').html('Timed out, please check your network connection.');
				$('#add-ques-alert').fadeIn('fast');

				setTimeout(function() {
					$('#add-ques-alert').fadeOut(function() {
						$('#add-ques-alert-context').removeClass('alert-danger');
						$('#add-ques-alert-content').html('');
					})
				}, 2500);
			}
		})
	})

	$('#qbank-tadd-submit').on('blur', function(e) {
		$('.form-inline, .editableform').submit();
	})

	$('#qbank-tadd-submit').on('click', function(e) {
		e.preventDefault();

		var is_brainstorm = $('#is_brainstorm').is(':checked');

		if ($('#qbank-tadd-text').editable('getValue', true).length == 0) $('#qbank-tadd-text').editable('setValue', $('#qbank-tadd-name').editable('getValue', true));

		$.ajax({
			type: 'POST',
			url: 'api?action=addQuestion',
			data: {
				'cat-id':           $('#cat-id').val(),
				'ques-name':        $('#qbank-tadd-name').editable('getValue', true),
				'ques-text':        $('#qbank-tadd-text').editable('getValue', true),
				'ques-fback':       $('#qbank-tadd-feedback').editable('getValue', true),
				'ques-beep':        $('#qbank-tadd-beep').prop('checked'),
				'ans0-text':        $('#qbank-tadd-ans0-text').editable('getValue', true),
				'ans0-correct':     true,
				'ans1-text':        is_brainstorm ? '' : $('#qbank-tadd-ans1-text').editable('getValue', true),
				'ans1-correct':     is_brainstorm ? false : $('#qbank-tadd-ans1-correct').prop('checked'),
				'ans2-text':        is_brainstorm ? '' : $('#qbank-tadd-ans2-text').editable('getValue', true),
				'ans2-correct':     is_brainstorm ? false : $('#qbank-tadd-ans2-correct').prop('checked'),
				'ans3-text':        is_brainstorm ? '' : $('#qbank-tadd-ans3-text').editable('getValue', true),
				'ans3-correct':     is_brainstorm ? false : $('#qbank-tadd-ans3-correct').prop('checked')
			},
			dataType: 'json',
			success: function(resp) {
				if (resp.onerror == undefined || resp.onerror) {
					alert(resp.msg == undefined ? 'General Error' : resp.msg);

				} else {
					$(
						'<tr id="question-row-' + resp.data.id + '">' +
						'    <td style="vertical-align: middle"><input class="qbank-bulk" type="checkbox" data-pk="' + resp.data.id + '" tabindex=-1> &nbsp;' + resp.data.id + '</td>' +
						'    <td><a href="#" class="qbank-edit editable-item" data-name="qbank-name" data-type="textarea" data-pk="' + resp.data.id + '" data-title="Question name">' + $('#qbank-tadd-name').editable('getValue', true) + '</a></td>' +
						'    <td><a href="#" class="qbank-edit editable-item" data-name="qbank-info" data-type="textarea" data-pk="' + resp.data.id + '" data-title="Question Text">' + $('#qbank-tadd-text').editable('getValue', true) + '</a></td>' +
						'   <td><a href="#" class="refresh2edit">Refresh page to edit</a></td>' +
						'    <td>' + ($('#qbank-tadd-beep').prop('checked') ? '<i class="fa fa-volume-up" aria-hidden="true"></i> ' : '') + '<a href="#" class="qbank-edit editable-item" data-name="qbank-feedback" data-type="textarea" data-pk="' + resp.data.id + '" data-title="General Feedback">' + $('#qbank-tadd-feedback').editable('getValue', true) + '</a></td>' +
						'    <td><a href="#" data-pk="' + resp.data.id + '" data-keyboard="true" class="qbank-edit-modal btn btn-info btn-xs"><i class="fa fa-paint-brush" aria-hidden="true"></i> Answer</a></td>' +
						'</tr>'
					).insertBefore($('#question-tadd-general'));

					$('a[class*="qbank-tadd"]').editable('setValue', '');
					$('input[class*="qbank-tadd-chkbox"][type="checkbox"]').prop('checked', false);

					$('td[class="qbank-edit"][data-pk="' + resp.data.id + '"]').editable('activate');
					$('.qbank-edit[data-pk="' + resp.data.id + '"]').css('border-bottom', 'dashed 1px #0088cc');

					applyEditable();
					
					$('#qbank-tadd-name').editable('setValue', '');
					$('#qbank-tadd-name').focus();
				}
			},
			error: function() {
				alert('Request error. Please check your fields and try again.');
				$('#qbank-tadd-name').editable('show');
			},
			timeout: function() {
				alert('Timed out, please check your network connection.');
				$('#qbank-tadd-name').editable('show');
			}
		})
	})

	$('.qbank-beep').on('change', function() {
		var obj = $(this);
		var qid = obj.attr('data-pk');
		var enable = obj.is(':checked') ? '1' : '0';
		var enabled = obj.prop('checked');
		
		$.ajax({
			type: 'POST',
			url: 'api?action=switchBeep',
			data: {
				'qid': qid,
				'enable': enable
			},
			dataType: 'json',
			success: function(resp) {
				if (resp.onerror == undefined || resp.onerror) {
					obj.prop('checked', !enabled);
					alert(resp.msg == undefined ? 'General Error' : resp.msg);
				}
			},
			error: function() {
				obj.prop('checked', !enabled);
				alert('Request error.');
			},
			timeout: function() {
				obj.prop('checked', !enabled);
				alert('Timed out.');
			}
		})
	})

	$('#qbank-selectall').on('change', function() {
		$('.qbank-bulk').prop('checked', this.checked);
	})

	$('.qbank-bulk').on('change', function() {
		$('#qbank-selectall').prop('checked', $('.qbank-bulk:checked').length >= $('.qbank-bulk').length);
	})

	$('.qbank-bulk').on('focus', function(e) {
		e.preventDefault();
		$(this).parent().parent().find('a[data-name="qbank-name"]').editable('submit');
	})

	$('#cat-selectall').on('change', function() {
		$('.cat-bulk').prop('checked', this.checked);
	})

	$('.cat-bulk').on('change', function() {
		$('#cat-selectall').prop('checked', $('.cat-bulk:checked').length >= $('.cat-bulk').length);
	})

	$('#qbank-bulk-exec').on('click', function(e) {
		e.preventDefault();

		var ids = '';
		$('.qbank-bulk:checked').each(function(i) {
			ids += $(this).attr('data-pk') + ',';
		})
		ids = ids.substring(0, ids.length - 1);

		if ($('#qbank-bulk-select').val() == null || ids.length < 1) return; // when bulk action not selected
		else {

			if ($('#qbank-bulk-select').val() == 'del' && confirm('Please confirm you will permanently delete selected questions!') == false) return;

			$.ajax({
				type: 'POST',
				url: 'api?action=bulkExec',
				data: {
					'method': $('#qbank-bulk-select').val(),
					'ids': ids,
					'scope': 'question'
				},
				success: function(resp) {
					if (resp.data.reload != undefined && resp.data.reload) window.location.reload();
					else alert('Operation successful.');
				},
				error: function() {
					alert('Request error.');
				},
				timeout: function() {
					alert('Timed out.');
				}
			})
		}
	})

	$('#cat-bulk-exec').on('click', function(e) {
		e.preventDefault();

		var ids = '';
		$('.cat-bulk:checked').each(function(i) {
			ids += $(this).attr('data-pk') + ',';
		})
		ids = ids.substring(0, ids.length - 1);

		if ($('#cat-bulk-select').val() == null || ids.length < 1) return; // when bulk action not selected
		else {

			if ($('#cat-bulk-select').val() == 'del' && confirm('Please confirm you will permanently delete selected categories and all questions belong to them!') == false) return;

			$.ajax({
				type: "POST",
				url: 'api?action=bulkExec',
				data: {
					'method': $('#cat-bulk-select').val(),
					'ids': ids,
					'scope': 'cat'
				},
				success: function(resp) {
					if (resp.data.reload != undefined && resp.data.reload) window.location.reload();
					else alert('Operation successful.');
				},
				error: function() {
					alert('Request error.');
				},
				timeout: function() {
					alert('Timed out.');
				}
			})
		}
	})

	$('#cat-bulk-select').on('change', function() {
		if ($(this).val() != null) $('#cat-bulk-exec').fadeIn();
	})

	$('#qbank-bulk-select').on('change', function() {
		if ($(this).val() != null) $('#qbank-bulk-exec').fadeIn();
	})

	$('.cat-add-dropdown').on('click', function(e) {
		e.preventDefault();

		var cat_name = $('#cat-add-name').editable('getValue', true);
		var cat_info = $('#cat-add-info').editable('getValue', true);

		if (cat_name.length <= 0) {
			alert('Category name is compulsory.');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: 'api?action=createCat',
			data: {
				name: cat_name,
				info: cat_info,
				cid:  $(this).attr('data-cid'), 
			},
			success: function(resp) {
				if (resp.onerror == undefined || resp.onerror) {
					alert(resp.msg == undefined ? 'General Error' : resp.msg);
				} else {
					$(
						'<tr>' +
						'    <td style="vertical-align: middle"><input class="cat-bulk" class="checkbox" type="checkbox" data-pk="' + resp.data.id + '"> &nbsp;' + resp.data.id + '</td>' +
						'    <td><a href="#" class="cat-edit editable-item" data-name="cat-name" data-type="text" data-pk="' + resp.data.id + '" data-title="Category name">' + cat_name + '</a></td>' +
						'    <td><a href="#" class="cat-edit editable-item" data-name="cat-info" data-type="text" data-pk="' + resp.data.id + '" data-title="Category info">' + cat_info + '</a></td>' +
						'    <td align="center"><a href="qbank?cid=' + resp.data.id + '" class="btn btn-info btn-xs">Manage</a></td>' +
						'</tr>'
					).insertBefore($('#table-bulk'));
					$('td[class="cat-edit"][data-pk="' + resp.data.id + '"]').editable('activate');
					$('.cat-edit[data-pk="' + resp.data.id + '"]').css('border-bottom', 'dashed 1px #0088cc');
					$('#cat-add-name').editable('setValue', '');
					$('#cat-add-info').editable('setValue', '');
					$('#cat-add-name').focus();

					applyEditable();
				}
			},
			error: function() {
				alert('Request error.');
			},
			timeout: function() {
				alert('Timed out.');
			}
		})
	})

	$('.cat-fixfback').on('click', function(e) {
		e.preventDefault();

		var c = confirm('Are you sure to proceed? This will overwrite any exisiting feedback text for beep questions.\r\n\r\nPress Cancel to cancel the operation.');

		if (c) {
			var cid = $(this).attr('data-cid');

			$.ajax({
				type: 'POST',
				url: 'api?action=fixCatFeedback',
				data: {
					cid: cid
				},
				success: function(resp) {
					if (resp.onerror == undefined || resp.onerror) {
						alert(resp.msg == undefined ? 'General Error' : resp.msg);
					} else {
						c = confirm('Operation Successful: ' + resp.data.total + ' questions have been fixed.\r\n\r\nPress Yes if you want to manage this category now.');

						if (c) window.location = './qbank?cid=' + cid;
					}
				},
				error: function() {
					alert('Request error.');
				},
				timeout: function() {
					alert('Timed out.');
				}
			})
		}
	})

	$(document).on('focus', '.qbank-edit-modal, #qbank-tadd-submit, #qbank-tadd-beep, .qbank-tadd-chkbox, .cat-manage, .cat-bulk', function(e) {
		$('.form-inline, .editableform').submit(); // editable focus fix
	})

	$('a[class*="editable-item"]').on('focus', function(e) {
		e.preventDefault();
		$(this).editable('show');
		$('.editable-input textarea').focus();
	})

	$(document).on('mouseover', 'a[class*="editable-item"]', function(e) {
		$(this).focus();
	})

	$('#is_brainstorm').on('change', function() {

		var obj = $(this);
		var checked = obj.is(':checked');

		if (checked) {
			for (var i = 1; i < 4; i++) $('#question-tadd-ans' + i).fadeOut();
			$('.question-tadd-generalfeedback').fadeOut();
			$('#qbank-tadd-beep').prop('checked', true);
			$('#qbank-tadd-beep').prop('disabled', true);
			$('#qbank-tadd-beep-label').fadeOut();
		} else {
			for (var i = 1; i < 4; i++) {
				$('#question-tadd-ans' + i).fadeIn();
				$('#qbank-tadd-ans' + i + '-text').editable('setValue', '');
				$('#qbank-tadd-ans' + i + '-correct').prop('checked', false);
			}
			$('.question-tadd-generalfeedback').fadeIn();
			$('#qbank-tadd-feedback').editable('setValue', '');
			$('#qbank-tadd-beep').prop('disabled', false);
			$('#qbank-tadd-beep-label').fadeIn();
		}
		
	})

	$(document).on('click', '.qbank-edit-modal', function(e) {
		e.preventDefault();

		var qid = $(this).attr('data-pk');

		$('#modal-edit-ansid').html(qid);

		$.ajax({
			type: 'POST',
			url: 'api?action=getAnswers',
			data: {
				'qid': qid
			},
			dataType: 'json',
			success: function(resp) {
				$('#modal-edit-submit').attr('data-qid', qid); // update button qid

				if (resp.onerror == undefined || resp.onerror) {
					alert(resp.msg == undefined ? 'General Error' : resp.msg);
				} else {
					// success: reset fields to default
					$('textarea[class*="edit-ques-ans"]').html('');
					$('input[class*="edit-ques-ans"][type="checkbox"]').prop('checked', false);

					for (var i = 0; i < resp.data.length; i++) {
						$('#edit-ques-ans-' + i + '-text').html(resp.data[i]['text']);
						$('#edit-ques-ans-' + i + '-text').attr('data-aid', resp.data[i]['aid']);
						$('#edit-ques-ans-' + i + '-correct').prop('checked', resp.data[i]['correct']);
					}

					$('#modal-edit').modal('show');
				}
			},
			error: function() {
				alert('Request error.');
			},
			timeout: function() {
				alert('Timed out.');
			}
		})

	})

	$('#modal-edit').on('shown.bs.modal', function() {
		$(this).find('[autofocus]').focus();
	});

	$(document).on('keyup', 'textarea[class="form-control input-large"]', function(e) {
		var exec = false;

		if (/^\ \ \ $/.test($(this).val())) {
			$(this).val('');
			exec = true;
		} else if (/\.\ \ $/.test($(this).val()) && $(this).val() != '.  ') {
			$(this).val($(this).val().replace(/\.\ \ $/,''));
			exec = true;
		}

		if (exec) {
			var curIndex = $('.editable-item').index($(this).parent().parent().parent().parent().parent().parent().parent().find('.editable-item'));
			curIndex++;
			
			$('textarea[class="form-control input-large"]').parent().parent().parent().parent().submit();
			$('.editable-item').eq(curIndex).focus();
		}
	})

	$(document).on('keyup', 'input[class="form-control input-sm"]', function(e) {
		var exec = false;

		if (/^\ \ \ $/.test($(this).val())) {
			$(this).val('');
			exec = true;
		} else if (/\.\ \ $/.test($(this).val()) && $(this).val() != '.  ') {
			$(this).val($(this).val().replace(/\.\ \ $/,''));
			exec = true;
		}

		if (exec) {
			var curIndex = $('.editable-item').index($(this).parent().parent().parent().parent().parent().parent().parent().find('.editable-item'));
			curIndex++;
			
			$('input[class="form-control input-sm"]').parent().parent().parent().parent().submit();
			$('.editable-item').eq(curIndex).focus();
		}
	})

	$(document).on('keyup', 'textarea[class="form-control edit-ques-ans"]', function(e) {
		var exec = false;

		if (/^\ \ \ $/.test($(this).val())) {
			$(this).val('');
			exec = true;
		} else if (/\.\ \ $/.test($(this).val()) && $(this).val() != '.  ') {
			$(this).val($(this).val().replace(/\.\ \ $/,''));
			exec = true;
		}

		if (exec) {
			var curIndex = $('textarea[class="form-control edit-ques-ans"]').index($(this));
			curIndex++;

			if (curIndex >= $('textarea[class="form-control edit-ques-ans"]').length) return false;
			
			$('textarea[class="form-control edit-ques-ans"]').eq(curIndex).focus();
		}
	})

	$(document).on('click', '.refresh2edit', function(e) {
		e.preventDefault();
		window.location.reload();
	})

	$(document).ready(function() {
		try {
			$('#qbank-tadd-name').focus();
			$('#qbank-tadd-name').editable('show');
			$('#qbank-tadd-name').editable('setValue', '');
		} catch(e) {}
		
		try {
			$('#add-ques-name').focus();
		} catch(e) {}
	})

});