<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>




  <link rel="stylesheet" href="/js/jquery-ui.css">
  <script src="/js/jquery-ui.js"></script>

  <script>
  $(function() {

var date = $('#datepicker').datepicker({ dateFormat: 'dd-mm-yy' }).val();

$( "#datepicker" ).datepicker( "option", "firstDay", 1 );


  });
  </script>







<script type="text/javascript">
$(document).ready(function() {
	
	//if submit button is clicked
	$('#submit').click(function () {		
		
		//Get the data from all the fields
		var name = $('input[name=name]');
		var email = $('input[name=email]');
		var tarix = $('input[name=tarix]');
		var website = $('select[name=website]');
		var comment = $('textarea[name=comment]');
		var razmer = $('input[name=razmer]');
		var mehsul = $('input[name=mehsul]');
		var haqqinda = $('textarea[name=haqqinda]');

		//Simple validation to make sure user entered something
		//If error found, add hightlight class to the text field
		
		if (name.val()=='') {
			name.addClass('hightlight');
			return false;
		} else name.removeClass('hightlight');
		
		if (email.val()=='') {
			email.addClass('hightlight');
			return false;
		} else email.removeClass('hightlight');

		if (website.val()=='') {
			website.addClass('hightlight');
			return false;
		} else website.removeClass('hightlight');

		if (tarix.val()=='') {
			tarix.addClass('hightlight');
			return false;
		} else tarix.removeClass('hightlight');

		if (razmer.val()=='') {
			razmer.addClass('hightlight');
			return false;
		} else razmer.removeClass('hightlight');
		
		if (comment.val()=='') {
			comment.addClass('hightlight');
			return false;
		} else comment.removeClass('hightlight');
		
		//organize the data properly
		var data = 'name=' + name.val() + '&razmer=' + razmer.val() + '&tarix=' + tarix.val() + '&mehsul=' + mehsul.val() + '&haqqinda=' + encodeURIComponent(haqqinda.val()) + '&email=' + email.val() + '&website=' + 
		website.val() + '&comment='  + encodeURIComponent(comment.val());
		
		//disabled all the text fields
		$('.text').attr('disabled','true');
		
		//show the loading sign
		$('.loading').show();
		
		//start the ajax
		$.ajax({
			//this is the php file that processes the data and send mail
			url: "/conn/process2.php",	
			
			//GET method is used
			type: "GET",

			//pass the data			
			data: data,		
			
			//Do not cache the page
			cache: false,
			
			//success
			success: function (html) {				
				//if process2.php returned 1/true (send mail success)
				if (html==1) {					
					//hide the form
					$('.form').fadeOut('slow');					
					
					//show the success message
					$('.done').fadeIn('slow');
					
				//if process2.php returned 0/false (send mail failed)
				} else alert('Sorry, unexpected error. Please try again later.');				
			}		
		});
		
		//cancel the submit button default behaviours
		return false;
	});	
});	
</script>
<script type="text/javascript">

    $(document).ready(function () {
        $("span.olcu_qutusu").click(function () {
            $(this).siblings("span.olcu_qutusu").removeClass("selected");
            $(this).addClass("selected");
        });
    });

</script>


<style>

.btn-pink2 {
    padding: 4px 20px;
    display: inline-block;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    -khtml-border-radius: 6px;
    border-radius: 6px;
    position: relative;
    text-align: center;
    text-decoration: none;
    border: 1px solid #03A9F4;
    -webkit-transition: all 0.2s ease;
    -moz-transition: all 0.2s ease;
    -o-transition: all 0.2s ease;
    transition: all 0.2s ease;
    background: #6EDCEA;
}

.btn-pink2:hover{
cursor:pointer;
    background: #7BD0EA;
}

.selected {
    background-color: #FFE60A !important;
}

.olcu_qutusu {
    background-color: white;
    color: #4c4a4b;
    line-height: 26px;
    padding: 5px 3px;
    margin-right: 3px !important;
    cursor: pointer;
    border: solid 1px #bfbfbf;
    min-height: 26px;
    min-width: 25px;
    text-align: center;
}

.block {
	width:430px;
	margin:0 auto;
	text-align:left;
border:3px dashed #19B9EA;
padding:10px;
}
.element * {
	margin:1px; 
	font-size:14px;
font-family:Verdana;
}
.element label {
	float:left; 
	width:155px;
    margin-top: 8px;
}
.element input.text {
	float:left; 
	width:250px;
	padding-left:10px;
height:27px;
}
.element .textarea {
	height:120px; 
	width:240px;
	padding-left:20px;
}
.element .hightlight {
	border:2px solid red !important;
	background:url() no-repeat 2px
}
.element #submit {
    margin-top: 24px;
    margin-right: 10px;
}
.loading {
	float:right; 
	height:28px; 
	width:28px; 
	display:none;
}
.done {    padding: 4px 3px;
    background-color: #C7FFC7;
    border-radius: 5px;
    border: 1px solid #52B352;
    margin: 0 !important;
    font-size: 13px;
    font-family: Verdana;
    text-align: center;
    width: 420px;
display:none;
}

</style>

<script>
   function changeValue(o){
     document.getElementById('type').value=o.innerHTML;
    }
</script>


</head>
<body>


<div class="block">
<div class="done">
<b>Randevu sifarişiniz qəbul olundu</b><br> Ən qısa zamanda operatorumuz təsdiqinizi almaq üçün sizinlə əlaqə saxlayacaq. <br><br> Təşəkkürlər! 
</div>
	<div class="form">
	<form method="post" action="/conn/process2.php">


<textarea style="display:none;" id="haqqinda" name="haqqinda" class="text"> <?php if(isset($_SERVER['HTTP_USER_AGENT'])) {  echo $_SERVER['HTTP_USER_AGENT']; } ?> </textarea>


	<div class="element">
		<label>Randevu tarixi:</label>
		<input type="text" name="tarix" class="text" id="datepicker"/>
	</div>
	<div class="element">
		<label>Adınız və Soyadınız:</label>
		<input type="text" name="name" class="text"/>
	</div>
	<div class="element">
		<label>Telefon nömrəniz:</label>
		<input type="text" name="email" class="text" />
	</div>
	<div class="element">
		<label>Xidmət növü:</label>
                <select name="website" class="text" style="width: 264px; padding: 8px;">
    <option name="website" value="Estetik stomatologiya">Estetik stomatologiya</option>
    <option name="website" value="Paradonologiya və paradontoloji cərrahiyyə">Paradonologiya və paradontoloji cərrahiyyə</option>
    <option name="website" value="Ortopediya">Ortopediya</option>
    <option name="website" value="Ortodontiya">Ortodontiya</option>
    <option name="website" value="İmplantologiya">İmplantologiya</option>
    <option name="website" value="Diaqnostik stomatologiya">Diaqnostik stomatologiya</option>
    <option name="website" value="Digər">Digər (mesajda qeyd edin)</option>
                </select>
	</div>
	<div class="element" style="padding-top: 17px;">
		<label><br>Mesajınız:</label>
		<textarea name="comment" class="text textarea" /></textarea>
	</div>
	<div class="element">
		
		<input class="btn-pink2 btn-submit f-right" type="submit" id="submit" value="Randevunu təsdiqlə"/>
		<div class="loading"></div>
	</div>
	</form>
	</div>
</div>





<div class="clear"></div>



</body>
</html>