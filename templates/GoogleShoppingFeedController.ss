<?xml version="1.0"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>$SiteConfig.Title</title>
        <link>$BaseHref</link>
        <description>$SiteConfig.Tagline</description>

        <% loop $Items %>
        <item>
            <title>$Title</title>
            <g:title>$Title</g:title>
            <description>$XMLSummary</description>
            <g:description>$XMLSummary</g:description>
            <g:id><% if $InternalItemID %>$InternalItemID<% else %>$ID<% end_if %></g:id>
            <link>$AbsoluteLink</link>
            <g:link>$AbsoluteLink</g:link>
            <% if $Image.exists %><g:image_link>{$Image.AbsoluteLink}</g:image_link><% end_if %>
            <g:price>$Price(2) $Top.Currency</g:price>
            <g:condition>New</g:condition>
            <g:availability><% if $IsAvailable %>in-stock<% else %>out of stock<% end_if %></g:availability>
            <g:brand>$Brand.Title</g:brand>
            <% if $MPN %><g:mpn>$MPN</g:mpn><% end_if %>
            <% if $GoogleProductCategory.exists %><g:google_product_category>$GoogleProductCategory.GoogleID
            </g:google_product_category><% end_if %>
            <g:custom_label_1>$Parent.Title</g:custom_label_1>
        </item>
        <% end_loop %>

    </channel>
</rss>