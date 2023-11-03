# Notes from cypress reports

## Manage Pods screen

Command:      a11y error!
Id:           color-contrast
Impact:       serious
Tags:         Array(8)
Description:  Ensures the contrast between foreground and background colors meets WCAG 2 AA minimum contrast ratio thresholds
Help:         Elements must meet minimum color contrast ratio thresholds
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/color-contrast?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Element has insufficient color contrast of 1.96 (foreground color: #f6f6e8, background color: #95bf3b, font size: 9.8pt (13px), font weight: normal). Expected contrast ratio of 4.5:1"

html: 
"<a href=\"https://friends.pods.io/donations/become-a-friend/?utm_source=pods_plugin_callout&amp;utm_medium=link&amp;utm_campaign=friends_2023_docs\" target=\"_blank\" rel=\"noreferrer\" class=\"pods-admin_friends-callout_button--join\">\n\t\t\t\tDonate Now »\n\t\t\t</a>"



---

Command:      a11y error!
Id:           link-name
Impact:       serious
Tags:         (12) ['cat.name-role-value', 'wcag2a', 'wcag244', 'wcag412', 'section508', 'section508.22.a', 'TTv5', 'TT6.a', 'EN-301-549', 'EN-9.2.4.4', 'EN-9.4.1.2', 'ACT']
Description:  Ensures links have discernible text
Help:         Links must have discernible text
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/link-name?application=axeAPI

failureSummary: 
"Fix all of the following:\n  Element is in tab order and does not have accessible text\n\nFix any of the following:\n  Element does not have text that is visible to screen readers\n  aria-label attribute does not exist or is empty\n  aria-labelledby attribute does not exist, references elements that do not exist or references elements that are empty\n  Element has no title attribute"

html: 
"<a href=\"/wp-admin/admin.php?page=pods&amp;pods_callout_dismiss=friends_2023_docs\" class=\"pods-admin_friends-callout_close\">"
impact

## Edit pod screen

Command:      a11y error!
Id:           aria-command-name
Impact:       serious
Tags:         Array(8)
Description:  Ensures every ARIA button, link and menuitem has an accessible name
Help:         ARIA commands must have an accessible name
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/aria-command-name?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Element does not have text that is visible to screen readers\n  aria-label attribute does not exist or is empty\n  aria-labelledby attribute does not exist, references elements that do not exist or references elements that are empty\n  Element has no title attribute"

html: 
"<div class=\"pods-field-group_handle\" role=\"button\" tabindex=\"0\" aria-disabled=\"false\" aria-roledescription=\"sortable\" aria-describedby=\"DndDescribedBy-0\"><span class=\"dashicon dashicons dashicons-menu\"></span></div>"

--- 

Command:      a11y error!
Id:           color-contrast
Impact:       serious
Tags:         (8) ['cat.color', 'wcag2aa', 'wcag143', 'TTv5', 'TT13.c', 'EN-301-549', 'EN-9.1.4.3', 'ACT']
index-21ca8685.js:133730 Description:  Ensures the contrast between foreground and background colors meets WCAG 2 AA minimum contrast ratio thresholds
Help:         Elements must meet minimum color contrast ratio thresholds
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/color-contrast?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Element has insufficient color contrast of 4.04 (foreground color: #007cba, background color: #f1f1f1, font size: 9.8pt (13px), font weight: normal). Expected contrast ratio of 4.5:1"

html: 
"<button type=\"button\" aria-label=\"Edit the slug\" class=\"components-button is-secondary\">Edit</button>"

--- 

Command:      a11y error!
Id:           nested-interactive
Impact:       serious
Tags:         (7) ['cat.keyboard', 'wcag2a', 'wcag412', 'TTv5', 'TT6.a', 'EN-301-549', 'EN-9.4.1.2']
Description:  Ensures interactive controls are not nested as they are not always announced by screen readers or can cause focus problems for assistive technologies
Help:         Interactive controls must not be nested
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/nested-interactive?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Element has focusable descendants"

html: 
"<div tabindex=\"0\" role=\"button\" class=\"pods-field-group_title\" aria-label=\"Press and hold to drag this item to a new position in the list\">"


### Edit pod screen - add new group

Command:      a11y error!
Id:           aria-command-name
Impact:       serious
Tags:         Array(8)
Description:  Ensures every ARIA button, link and menuitem has an accessible name
Help:         ARIA commands must have an accessible name
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/aria-command-name?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Element does not have text that is visible to screen readers\n  aria-label attribute does not exist or is empty\n  aria-labelledby attribute does not exist, references elements that do not exist or references elements that are empty\n  Element has no title attribute"

html: 
"<span tabindex=\"0\" role=\"button\" class=\"pods-help-tooltip__icon\" aria-expanded=\"false\"><span class=\"dashicon dashicons dashicons-editor-help\"></span></span>"


failureSummary: 
"Fix any of the following:\n  Element does not have text that is visible to screen readers\n  aria-label attribute does not exist or is empty\n  aria-labelledby attribute does not exist, references elements that do not exist or references elements that are empty\n  Element has no title attribute"

html: 
"<span tabindex=\"0\" role=\"button\" class=\"pods-help-tooltip__icon\" aria-expanded=\"false\"><span class=\"dashicon dashicons dashicons-editor-help\"></span></span>"

--- 

Command:      a11y error!
Id:           aria-required-children
Impact:       critical
Tags:         (5) ['cat.aria', 'wcag2a', 'wcag131', 'EN-301-549', 'EN-9.1.3.1']
Description:  Ensures elements with an ARIA role that require child roles contain them
Help:         Certain ARIA roles must contain particular children
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/aria-required-children?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Element has children which are not allowed: [role=button]\n  Element uses aria-busy=\"true\" while showing a loader"
html: 
"<div class=\"pods-settings-modal__tabs\" role=\"tablist\" aria-label=\"Pods Field Group Settings\">"

--- 

Command:      a11y error!
Id:           aria-valid-attr-value
Impact:       critical
Tags:         (5) ['cat.aria', 'wcag2a', 'wcag412', 'EN-301-549', 'EN-9.4.1.2']
Description:  Ensures all ARIA attributes have valid values
Help:         ARIA attributes must conform to valid values
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/aria-valid-attr-value?application=axeAPI

failureSummary: 
"Fix all of the following:\n  Invalid ARIA attribute value: aria-controls=\"basic-tab\""

html: 
"<div class=\"pods-settings-modal__tab-item pods-settings-modal__tab-item--active\" aria-controls=\"basic-tab\" role=\"button\" tabindex=\"0\">Group Details</div>"


failureSummary: 
"Fix all of the following:\n  Invalid ARIA attribute value: aria-controls=\"advanced-tab\""

html: 
"<div class=\"pods-settings-modal__tab-item\" aria-controls=\"advanced-tab\" role=\"button\" tabindex=\"0\">Advanced</div>"

---

Command:      a11y error!
Id:           color-contrast
Impact:       serious
Tags:         (8) ['cat.color', 'wcag2aa', 'wcag143', 'TTv5', 'TT13.c', 'EN-301-549', 'EN-9.1.4.3', 'ACT']
Description:  Ensures the contrast between foreground and background colors meets WCAG 2 AA minimum contrast ratio thresholds
Help:         Elements must meet minimum color contrast ratio thresholds
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/color-contrast?application=axeAPI

'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…t weight: bold). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…t weight: bold). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1', 'Fix any of the following:\n  Element has insufficie…weight: normal). Expected contrast ratio of 4.5:1'

--- 

Command:      a11y error!
Id:           heading-order
Impact:       moderate
Tags:         (2) ['cat.semantics', 'best-practice']
Description:  Ensures the order of headings is semantically correct
Help:         Heading levels should only increase by one
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/heading-order?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Heading order invalid"

html: 
"<h3 class=\"pods-form-ui-heading pods-form-ui-heading-visibility\" id=\"heading-visibility\">Visibility</h3>"

## Edit pod screen - add field

Command:      a11y error!
Id:           aria-command-name
Impact:       serious
Tags:         (8) ['cat.aria', 'wcag2a', 'wcag412', 'TTv5', 'TT6.a', 'EN-301-549', 'EN-9.4.1.2', 'ACT']
Description:  Ensures every ARIA button, link and menuitem has an accessible name
Help:         ARIA commands must have an accessible name
Helpurl:      https://dequeuniversity.com/rules/axe/4.8/aria-command-name?application=axeAPI

failureSummary: 
"Fix any of the following:\n  Element does not have text that is visible to screen readers\n  aria-label attribute does not exist or is empty\n  aria-labelledby attribute does not exist, references elements that do not exist or references elements that are empty\n  Element has no title attribute"

html: 
"<span tabindex=\"0\" role=\"button\" class=\"pods-help-tooltip__icon\" aria-expanded=\"false\"><span class=\"dashicon dashicons dashicons-editor-help\"></span></span>"

