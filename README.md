# TYPO3 Extension dragon_drop

## ViewHelper

### PasteLink

This viewhelper can be used within backend templates to provide a paste link for "clipped" CEs within mask container elements.

```xml
<html data-namespace-typo3-fluid="true"
      xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
      xmlns:dnd="http://typo3.org/ns/Kitzberger/DragonDrop/ViewHelpers">

<div class="mask-accordion">
    <dnd:be.pasteLink target="{processedRow}" override="{colPos:999, tx_mask_accordion_items_parent: processedRow.uid}" />

    <ul>
        <f:for each="{processedRow.tx_mask_accordion_items}" as="item">
            <li>
                {item.header} (id={item.uid})
            </li>
        </f:for>
    </ul>
</div>
```

This'll render an extra paste button into the accordion element:

![page module](Documentation/Images/page-module.png)

In case you don't want the paste record to be hidden, you can override the `hidden` property with 0 via the `override` attribute, e.g.

```xml
<dnd:be.pasteLink target="{processedRow}" override="{colPos:999, tx_mask_accordion_items_parent: processedRow.uid, hidden:0}" />
```

In case you don't want the button text to be that paste icon, you can set a different button text:

```xml
<dnd:be.pasteLink target="{processedRow}" override="{colPos:999, tx_mask_accordion_items_parent: processedRow.uid, hidden:0}">Paste here</dnd:be.pasteLink>
```
