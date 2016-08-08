var startObj = new Object();
var selectedObjs = new Array();
var getagendatableUrl = '';
var getmodalcontentUrl = '';
var gettimeendUrl = '';
var customerId = '';
var locationId = '';
var currentDate = '';
var imagesDir = '';
var flAdd = false;

function printTime(h, m) {
	h = h.toString();
	if (h.length < 2)
		h = '0' + h;

	m = m.toString();
	if (m.length < 2)
		m = '0' + m;
	return '' + h + ':' + m;
}

function selectStart(tdObj, employeeId, h, m) {
	startObj.tdObj = tdObj;
	startObj.employeeId = employeeId;
	startObj.h = h;
	startObj.m = m;
	$(startObj.tdObj).addClass('selected');
}

function selectContinue(tdObj, employeeId, h, m) {
	if (startObj.tdObj) {
		flAdd=true;
		
		if (startObj.employeeId == employeeId) {
			if (h > startObj.h || h == startObj.h && m > startObj.m) {
				hMin = startObj.h;
				hMax = h;
				mMin = startObj.m;
				mMax = m;
			} else {
				hMax = startObj.h;
				hMin = h;
				mMax = startObj.m;
				mMin = m;
			}
			alreadySelected = false;

			for (elem in selectedObjs) {
				myObj = selectedObjs[elem];

				if (myObj.h < hMin || myObj.h == hMin && myObj.m < mMin
						|| myObj.h > hMax || myObj.h == hMax && myObj.m > mMax) {
					$(myObj.tdObj).removeClass('selected');
					selectedObjs.splice(elem, 1);
				} else {
					if (myObj.employeeId == employeeId && myObj.h == h
							&& myObj.m == m)
						alreadySelected = true;
				}
			}

			if (!alreadySelected) {
				var selectedObj = new Object();
				selectedObj.tdObj = tdObj;
				selectedObj.employeeId = employeeId;
				selectedObj.h = h;
				selectedObj.m = m;
				$(selectedObj.tdObj).addClass('selected');
				l = selectedObjs.length;
				selectedObjs[l] = selectedObj;
			}
		} else {
			for (elem in selectedObjs) {
				myObj = selectedObjs[elem];
				$(myObj.tdObj).removeClass('selected');
			}
			selectedObjs = new Array();

			$(startObj.tdObj).removeClass('selected');
			startObj.tdObj = tdObj;
			startObj.employeeId = employeeId;
			startObj.h = h;
			startObj.m = m;
			$(startObj.tdObj).addClass('selected');
		}
	}
}

function selectEnd(tdObj, employeeId, h, m) {
	if (startObj.tdObj) {
		if (flAdd) {
			if (customerId) {
				if (h > startObj.h || h == startObj.h && m > startObj.m) {
					hMin = startObj.h;
					hMax = h;
					mMin = startObj.m;
					mMax = m;
				} else {
					hMax = startObj.h;
					hMin = h;
					mMax = startObj.m;
					mMin = m;
				}
				
				$.post(getagendatableUrl, {
					action :'add',
					customer_id :customerId,
					location_id :locationId,
					employee_id :employeeId,
					date :currentDate,
					time_start :printTime(hMin, mMin),
					time_end :printTime(hMax, mMax)
				}, onAjaxSuccess);

				startObj = new Object();
				selectedObjs = new Array();
			} else
				alert('Select a customer first.');
			
			flAdd=false;
		}
	}
}

function selectClear() {
	if (startObj.tdObj) {
		$(startObj.tdObj).removeClass('selected');
	}
	for (elem in selectedObjs) {
		myObj = selectedObjs[elem];
		$(myObj.tdObj).removeClass('selected');
	}

	startObj = new Object();
	selectedObjs = new Array();
}

function onAjaxSuccess(result) {
	$("#agenda").empty();
	$("#agenda").append(result);
}

function showEditAppointment(id) {
	$.post(getmodalcontentUrl, {
		id :id
	}, onGetModalContentSuccess);
}

function onGetModalContentSuccess(result) {
	$("#editAppointment").empty();
	$("#editAppointment").append(result);
	
	var color=parseInt($('#appointmentStyle').val());
	
	$('#colorpicker').colorPicker(
	{
		defaultColor: color,
		columns: 20,		
		color: ['#d0d7dd', '#ffc1ab', '#c3ffba', '#c9e6ff'], 
		click:function(c){
			$('#appointmentStyle').val(c);		
		}
	});
	
	$('#editAppointment').modal();
}

function appointmentEdit(id) {
	if ($("#appointmentCancelled").attr('checked'))
		var chk = 1;
	else
		var chk = 0;

	$.post(getagendatableUrl, {
		action :'edit',
		id :id,
		appointmentTimeStart :$("#appointmentTimeStart").val(),
		appointmentTimeEnd :$("#appointmentTimeEnd").val(),
		appointmentNote :$("#appointmentNote").val(),
		appointmentStyle :$("#appointmentStyle").val()
	}, onAjaxSuccess);

	$.modal.close();
	startObj = new Object();
	selectedObjs = new Array();
}

function appointmentDelete(id) {
	$.post(getagendatableUrl, {
		action :'delete',
		id :id
	}, onAjaxSuccess);

	$.modal.close();
	startObj = new Object();
	selectedObjs = new Array();
}

function timeStartChanged() {
	$.getJSON(gettimeendUrl, {
		appointmentTimeStart :$("#appointmentTimeStart").val(),
		appointmentTimeEnd :$("#appointmentTimeEnd").val()
	}, onTimeStartSuccess);
}

function onTimeStartSuccess(result) {
	$("#appointmentTimeStart").val(result.timeStart);
	$("#appointmentTimeEnd").empty();
	$("#appointmentTimeEnd").append(result.timeEnd);
}

function hidePast() {
	$.post(getagendatableUrl, {
		past :'hide'
	}, onAjaxSuccess);

	startObj = new Object();
	selectedObjs = new Array();
}

function showPast() {
	$.post(getagendatableUrl, {
		past :'show'
	}, onAjaxSuccess);

	startObj = new Object();
	selectedObjs = new Array();
}

$.fn.agenda = function(p) {
	$(document).mouseup(selectClear);

	getagendatableUrl = p.getagendatableUrl;
	getmodalcontentUrl = p.getmodalcontentUrl;
	gettimeendUrl = p.gettimeendUrl;
	customerId = p.customerId;
	locationId = p.locationId;
	currentDate = p.currentDate;
	imagesDir = p.imagesDir;

	$.post(getagendatableUrl, {}, onAjaxSuccess);
}