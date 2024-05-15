<%@ page language="java" contentType="text/html; charset=ISO-8859-1"
	pageEncoding="ISO-8859-1"%>
<%@ taglib prefix="s" uri="/struts-tags"%>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Formation ED - Accueil</title>
<link href="<s:url value="/css/template_css.css"/>" rel="stylesheet" type="text/css" />
<link href="<s:url value="/css/menu.css"/>" rel="stylesheet" type="text/css" />
<link type="image/x-icon" rel="icon" href="<s:url value="/images/ico.ico"/>" />
</head>
<body>

	<div id="headerdm">
		<h1>
			<a href="http://formationed.fr/">DirectMedica</a> 
			<img alt="Formation Ã  distance" style="width: 230px; height: 35px" src="image/logoFD.png">

		</h1>
	</div>


   <div align="center">
		<ul id="menu">
		        <li><a href="index.jsp">Accueil</a></li>
		        <li><a href="plan.jsp">Plan du site</a></li>
		     
		</ul>
	</div>	

	<div id="container">

		<div id="content_container">

			<div id="slider" style="overflow: hidden;">
				<!-- div id="header">
					<div id="headerbottom"></div>
				   </div-->
				<div align="center">
					<img alt="" style="margin-top:20px" width="220px" height="260px" src="image/med.jpg" />
				</div>
			</div>

			<div id="content">




				<div id="contentcenter">
					<script language="javascript">
						function validateForm() {
							if (document.getElementById("code").value == '') {
								alert('Veuillez saisir un code.');
							} else {
								document.getElementById("adobeForm").submit();
							}
						}
					</script>
					<center style="font-weight: bold;">
						<p>Code d'acc&egrave;s:</p>
						<p>
							<s:form action="validateIndexAction" id="adobeForm" method="POST" theme="simple" namespace="/">

								<p>
									<input type="text" id="code" name="code" class="access" />
								</p>
								<p>
									<input type="button" onclick="validateForm();" value="Valider"	class="button" /> 
									<input type="hidden" name="action" value="login" /> 
									<input type="hidden" name="flashversion" id="flashversion" value="0" />
							</s:form>
						<div class="errors">
							<s:actionerror />
						</div>
						</p>
					</center>
				</div>

			</div>



			<div id="footer">
				<div class="footer_box" style="text-align: left; width: 50%;">
					<a style="color: #49639B; font-size: 12px;"
						onclick="alert( 'Cette plateforme de formation &agrave; distance respecte les principes &eacute;nonc&eacute;s dans la loi n&deg; 2004-575 du 21 juin 2004, relative &agrave; la confiance dans l&acute;&eacute;conomie num&eacute;rique. Conform&eacute;ment &agrave; la loi &laquo; informatique et libert&eacute;s &raquo; du 6 janvier 1978 modifi&eacute;e en 2004, vous b&eacute;n&eacute;ficiez d&acute;un droit d&acute;acc&egrave;s et de rectification aux informations qui vous concernent' );"
						href="#">Protection des donn&eacute;es personnelles</a>
				</div>
				<div class="footer_box" style="text-align: right; width: 50%;">
					<a style="color: #49639B; font-size: 12px;"
						href="mailto:webadmin@test.com" href="#"> Signalez un
						dysfonctionnement</a>
				</div>
				<br /> <br />  
				<div align="center"
					style="text-align: center; font-weight: bold; font-size: 9pt; color: #49639B;">Date
					de publication : 17/04/2014</div>


				<div id="footerdm">
					<p>&copy; Copyright 2014</p>

				</div>
			</div>
		</div>
	</div>
</body>
</html>
