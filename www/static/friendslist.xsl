<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml">
  
 <xsl:output method="xml" indent="yes" encoding="UTF-8"/>
 <xsl:param name="v"/>
	 
	 <!-- Main output -->
	 
	  <xsl:template match="/friends">
		<html>
		  <head> <title>Friends online</title> </head>
		  <body>
		  
			<h1>Friends Online</h1>
			<table style="margin-left:50px;" cellspacing="5">
			  <xsl:apply-templates select="friend" />
			</table>
			
		  </body>
		</html>
	  </xsl:template>
	 
	 
	 
	 <!-- Template for  friends -->
	 
	   <xsl:template match="friend">
	   
			<xsl:choose>
			<xsl:when test="@server_name = ''">
			
			 <tr style="color:gray">
			  <td><xsl:value-of select="@name"/></td>
			  <td width="45" align="center"> — </td>
			  <td>

					<xsl:choose>
					<xsl:when test="@last_update &lt; 60">
					  <xsl:value-of select="@last_update"/> <xsl:text> minutes ago</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="floor(@last_update div 60)"/> <xsl:text> hours ago</xsl:text>
					</xsl:otherwise>
					</xsl:choose>

			  </td>
			</tr>
			
			</xsl:when>
			<xsl:otherwise>
			
			<tr>
			  <td><xsl:value-of select="@name"/></td>
			  <td width="45" align="center"> — </td>
			  <td><xsl:value-of select="@server_name"/></td>
			</tr>
			
			</xsl:otherwise>
			</xsl:choose>
			
	   
	  </xsl:template>
 
 
 
</xsl:stylesheet>
