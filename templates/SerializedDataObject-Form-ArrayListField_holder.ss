<div id="$HolderID" class="field<% if $extraClass %> $extraClass<% end_if %>" $AttributesHTML>
	<% if $Title %><label class="left" for="$ID">$Title</label><% end_if %>
	<div class="middleColumn">
		$Field
	</div>
	<% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<% if $Description %><span class="description">$Description</span><% end_if %>
</div>
