# TYPO3 Extension dragon_drop

## PasteLink ViewHelper

This viewhelper can be used within backend templates to provide a paste link for "clipped" CEs within mask container elements.

```xml
<html data-namespace-typo3-fluid="true"
      xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
      xmlns:dnd="http://typo3.org/ns/Kitzberger/DragonDrop/ViewHelpers">

<div class="mask-accordion">
    <dnd:be.pasteLink target="{row}"
                      override="{colPos:999, tx_mask_accordion_items_parent: row.uid}" />

    <ul>
        <f:for each="{data.tx_mask_accordion_items}" as="item">
            <li>
                {item.header} (id={item.uid})
            </li>
        </f:for>
    </ul>
</div>
```

This'll render an extra paste button into the accordion element:

![page module](Documentation/Images/page-module.png)

These are the mandatory attributes

* `target` which needs to be set to a array representing the target record. Currently only the array key `pid` is being used internally.
* `override` which contains the field modifications the newly created record will be updated with. For EXT:mask containers that's at least colPos=999 and the "parent field".

This extension comes with a DataHandler hook that updates the "children count" field of the container after attaching the copied CE to it. The name of said "children count" field needs specified for the hook to properly do its job 2. You can do so by adding two attributes to the viewhelpers tag:

* `irreChildrenField` (fieldname of container element that contains that "children count")
* `irreParentField` (fieldname of child element that refers to container element)

```xml
    <dnd:be.pasteLink target="{row}"
                      override="{colPos:999, tx_mask_accordion_items_parent: row.uid}"
                      irreChildrenField="tx_mask_accordion_items"
                      irreParentField="tx_mask_accordion_items_parent" />
```

In case of EXT:mask the viewhelper tries to guess those parameters so you can try without specfying them.

### Compatibility with EXT:mask

In order to make this work with EXT:mask you need to make sure that the "parent field" is present in TCA. Please check the configuration module in the backend.

If it's not present (yet) you need to provide it yourself, see [github.com/Gernott/mask/issues/389](https://github.com/Gernott/mask/issues/389) for details.

### Prevent hidden records

In case you don't want the paste record to be hidden, you can override the `hidden` property with 0 via the `override` attribute, e.g.

```xml
<dnd:be.pasteLink target="{row}"
                  override="{colPos:999, tx_mask_accordion_items_parent: row.uid, hidden:0}" />
```

### Customize button text

In case you don't want the button text to be that paste icon, you can set a different button text:

```xml
<dnd:be.pasteLink target="{row}" ...>
    Paste here
</dnd:be.pasteLink>
```
