![attrchange](http://meetselva.github.io/images/attrchange.png)

attrchange is a simple jQuery plugin to bind a listener function on attribute change of an element. The handler function is triggered when an attribute is added, removed or modified to the element.  

This core attrchange plugin uses one of the following compatible methods based on the browser to detect an attribute change,

1. Mutation Observer 
2. DOMAttrModified
3. onpropertychange

##### Latest Updates:
The plugin code is majorly refactored to support extensions. Extensions are simple independent addons to the core plugin to performs a specific function.

In the [last release](https://github.com/meetselva/attrchange/releases), The plugin code is refactored to move the extensions code to a new file [attrchange_ext.js] (https://raw.githubusercontent.com/meetselva/attrchange/6e2cd8df1c5e365b6fd0400ae270a1a6955f4785/attrchange.js). The core plugin attrchange.js should still work independently.

Following are some of the useful addon added to the extensions,

1. **Polling:** Added Polling to detect attrchange by polling
   * This technique is extensive but useful at times based on the requirement
   * Added 2 modes, 1. Simple polling 2. Computed polling
   * Simple Polling is the basic polling where the callback is triggered when an attribute is modified programmatically
   * Computed Polling is extensive but useful to detect any **style property** changes
2. **Disconnect:** Allows to stop listenting when an attribute is modified
   * 2 modes of disconnect 1. Physical 2. Logical
   * Logical disconnect is a temporary disconnect. We can re-connect to the handler anytime using the reconnect function
   * Physical disconnect is permanent and cannot be reconnected, however you can establish a new connection anytime
3. **Re-connect:** Allows to re-connect a connection that was logically disconnected
4. **Remove:** is same as Physical disconnect that removes the listener function
5. **Get Properties:** Returns an object with information about the connection
   * **method** Returns a String information about the method that is used to detect an attribute change. It should be one of the values in ['propertychange', 'DOMAttrModified', 'Mutation Observer', 'polling']
   * **isPolling** Returns a Boolean. True if the selected method is polling, else returns false
   * **pollingInterval** Returns an integer value of the polling interval.
   * **status** Returns a String information about the current connection status. It should be one of the values in ['connected', 'disconnected', 'removed']

> **Important Note:** The below documentation link is for the core plugin and the information about extension are not available yet. I am working on a [new website](http://meetselva.github.io/) for documenation :wink:, until then please refer to the samples under examples folder to know about extensions.

> Feel free to drop a comment or feedback at http://meetselva.github.io/#disqus or report an issue at https://github.com/meetselva/attrchange/issues

##### Full Documentation: 
http://meetselva.github.com/attrchange/
