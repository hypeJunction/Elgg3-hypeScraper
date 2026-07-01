import elgg from 'elgg';
import embed from 'elgg/embed';
import lightbox from 'elgg/lightbox';
import $ from 'jquery';
import Ajax from 'elgg/Ajax';

const ajax = new Ajax();

$(document).on('submit', '.elgg-form-embed-player', function (e) {
	e.preventDefault();

	var $elem = $(this);

	var textAreaId = embed.textAreaId;
	var textArea = $('#' + textAreaId);

	var value = textArea.val();
	var result = textArea.val();

	ajax.action($elem.attr('action'), {
		data: ajax.objectify($elem)
	}).done(function (content) {
		textArea.focus();
		if (!elgg.isNullOrUndefined(textArea.prop('selectionStart'))) {
			var cursorPos = textArea.prop('selectionStart');
			var textBefore = value.substring(0, cursorPos);
			var textAfter = value.substring(cursorPos, value.length);
			result = textBefore + content + textAfter;
		} else if (document.selection) {
			// IE compatibility
			var sel = document.selection.createRange();
			sel.text = content;
			result = textArea.val();
		}

		if (result || result === '') {
			textArea.val(result);
		}

		lightbox.close();
	});

});
