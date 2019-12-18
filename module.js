// JavaScript Document.
$(function(){
    $('#fitem_id_cohort').hide();
    $('#id_assign_method').change(function(){
        var val = $(this).val();
        if(val == 1) {
            $('#fitem_id_cohort').hide();
            $('#fitem_id_u_id').show();
        } else {
            $('#fitem_id_u_id').hide();
            $('#fitem_id_cohort').show();
        }
    });
    
     $('input[name="savecourseorder"]').click(function(){
        var lpstageid  = $(this).parent().attr('id');
        var data = [];
        var courseid;
        var lp_id;
        var c=0;
        var selector = '#'+lpstageid+' .courseorder';
        $(selector).each(function(){
            var courseordername = $(this).attr('name');
            courseid = courseordername.split('_')[1];
            lp_id = courseordername.split('_')[2];
            var courseorder = $('input[name="'+courseordername+'"]').val();
            data[c++] = {lpid:lp_id, course:courseid, courseorder:courseorder};
        });
            var jsondata;
            jsondata = JSON.stringify(data);
            $.ajax({
                method:'POST',
                url:M.cfg.wwwroot+'/blocks/systemreports/ajax_bridge.php',
                data:{'action':'savecourseorder', 'data':jsondata },
                success:function(result){
                	console.log('success');
            }
            });
        
    });

     $('.removecourse').click(function(){
     	var conf = confirm("Are you sure you want to delete this course?");
     	var id = $(this).attr('id');
     	var courseid = id.split('_')[1];
     	var lpid = id.split('_')[2];
		if (conf == true) {
		  $.ajax({
                method:'POST',
                url:M.cfg.wwwroot+'/blocks/systemreports/ajax_bridge.php',
                data:{'action':'removecourse', 'courseid':courseid, 'lpid':lpid },
                success:function(result){
                	alert('Course deleted successfully');
                	$('#'+id).parent('.coursewrapper').remove();	
            }
            });
		} else {
			return false;
		}

     });
     
    $(".btn-primary").click(function () {
        var id = $(this).attr('id');
        if(id == 'courses-list'){
     	var userid = $(this).attr('userid');
        var startyear = $(this).attr('startyear');
        var endyear = $(this).attr('currentyear');
                        $.ajax({
                url: 'ajax_bridge.php',
                type: 'POST',
                data: 'action=getCourses&userid=' + userid + '&startyear=' + startyear + '&endyear=' + endyear,
                success: function (data) {
                 var obj = jQuery.parseJSON(data);
                 var courses = obj.result;
                 $( ".modal-body" ).html( courses );
                }
            });
        }
        if(id == 'systemenrolledcourses'){
            var startyear = $(this).attr('startyear');
            var endyear = $(this).attr('currentyear');
               $.ajax({
                url: 'ajax_bridge.php',
                type: 'POST',
                data: 'action=getenrolledCourses&startyear=' + startyear + '&endyear=' + endyear,
                success: function (data) {
                 var obj = jQuery.parseJSON(data);
                 var courses = obj.result;
                 $( ".modal-body" ).html( courses );
                }
            });   
        }
     });
});

