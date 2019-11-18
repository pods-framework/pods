var tribe = typeof tribe === "object" ? tribe : {}; tribe["common"] = tribe["common"] || {}; tribe["common"]["icons"] =
/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 393);
/******/ })
/************************************************************************/
/******/ ({

/***/ 2:
/***/ (function(module, exports) {

module.exports = React;

/***/ }),

/***/ 393:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: external "React"
var external_React_ = __webpack_require__(2);
var external_React_default = /*#__PURE__*/__webpack_require__.n(external_React_);

// CONCATENATED MODULE: ./src/modules/icons/tec.svg
var _extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function _objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var tec = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = _objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", _extends({ xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 29.99 39.98" }, props), external_React_default.a.createElement("defs", null, external_React_default.a.createElement("clipPath", { id: "a", transform: "translate(-984 -154.02)" }, external_React_default.a.createElement("path", { className: styles["cls-1"] || "cls-1", d: "M989 159.02h19.99V189H989z" })), external_React_default.a.createElement("clipPath", { id: "b", transform: "translate(-984 -154.02)" }, external_React_default.a.createElement("path", { className: styles["cls-1"] || "cls-1", d: "M0 0h1281v1258H0z" })), external_React_default.a.createElement("clipPath", { id: "c", transform: "translate(-984 -154.02)" }, external_React_default.a.createElement("path", { className: styles["cls-1"] || "cls-1", d: "M989 159h20v31h-20z" })), external_React_default.a.createElement("clipPath", { id: "d", transform: "translate(-984 -154.02)" }, external_React_default.a.createElement("path", { d: "M1005.81 159a3.24 3.24 0 0 0-3.18 3.28v6.42a3 3 0 0 0-1.36-.32 3.1 3.1 0 0 1-4.54 0 3 3 0 0 0-1.36.32v-6.4a3.18 3.18 0 1 0-6.36 0v16.42a10 10 0 1 0 20 .1.65.65 0 0 0 0-.1V162.3a3.24 3.24 0 0 0-3.2-3.3zm-1.36 3.28a1.36 1.36 0 1 1 2.73 0v12.1a5.84 5.84 0 0 0-2.73-1.22zm-4.54 9.38a1.36 1.36 0 1 1 2.73 0v1.41h-2.74zm-4.54 0a1.36 1.36 0 1 1 2.73 0v1.41h-2.73zm3.63 15.5a8.32 8.32 0 0 1-8.17-8.44V162.3a1.36 1.36 0 1 1 2.73 0V174a6.53 6.53 0 0 0 .65 2.78 5 5 0 0 0 4.79 2.85h.33a5.59 5.59 0 0 0-1.24 3.75.91.91 0 1 0 1.82 0 3.54 3.54 0 0 1 3.63-3.75.94.94 0 0 0 0-1.88H999a3.42 3.42 0 0 1-2.55-.94 3.84 3.84 0 0 1-1-1.88h8.06a4.22 4.22 0 0 1 .91.12 3.29 3.29 0 0 1 2.64 2.69 5 5 0 0 1 .08.94 9.11 9.11 0 0 1 0 .94 8.3 8.3 0 0 1-8.13 7.51z", clipRule: "evenodd", fill: "none" })), external_React_default.a.createElement("clipPath", { id: "e", transform: "translate(-984 -154.02)" }, external_React_default.a.createElement("path", { className: styles["cls-1"] || "cls-1", d: "M989 159h20v30h-20z" }))), external_React_default.a.createElement("g", { "data-name": "Layer 2" }, external_React_default.a.createElement("g", { "data-name": "Layer 1" }, external_React_default.a.createElement("path", { d: "M8.4 6.07l-2 .83-.25 19.88s1.71 3.33 1.88 3.54 3.83 2.79 3.83 2.79l4.75.46 5.42-3.21 1.5-3.83.58-6V7.77l-2.12-2-2.33 1.42-.13 9.38-2.21-1.17-2.37 1-1.8-1.42-2.71.67V6.86z", fill: "#fff" }), external_React_default.a.createElement("g", { clipPath: "url(#a)" }, external_React_default.a.createElement("g", { clipPath: "url(#b)" }, external_React_default.a.createElement("g", { clipPath: "url(#c)" }, external_React_default.a.createElement("g", { clipPath: "url(#d)" }, external_React_default.a.createElement("g", { clipPath: "url(#e)" }, external_React_default.a.createElement("path", { fill: "#020202", d: "M0 0h29.99v39.98H0z" })))))))));
});
// CONCATENATED MODULE: ./src/modules/icons/close.svg
var close_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function close_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var icons_close = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = close_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", close_extends({ width: "16", height: "16", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M14.36 15.78L8 9.41l-6.36 6.37-1.42-1.42L6.59 8 .22 1.64 1.64.22 8 6.59 14.36.23l1.41 1.41L9.41 8l6.36 6.36z", fill: "#191E23" }));
});
// CONCATENATED MODULE: ./src/modules/icons/alert.svg
var alert_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function alert_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var icons_alert = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = alert_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", alert_extends({ width: "19", height: "17", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M10.632 12.074H8.388l-.391-6.33c0-.5.675-.905 1.507-.905.832 0 1.507.405 1.507.904l-.379 6.33zm-.092 2.96c-.247.206-.593.31-1.037.31-.449 0-.8-.104-1.054-.31-.254-.206-.38-.492-.38-.86 0-.371.121-.66.367-.866.244-.206.6-.308 1.067-.308.462 0 .813.103 1.05.308.239.206.358.496.358.866 0 .368-.123.654-.37.86zm8.42.614L10.344.618C10.117.313 9.81 0 9.504 0c-.307 0-.613.312-.84.619L.032 15.675c-.082.316-.06.831.72 1.222h17.494c.805-.402.804-.936.714-1.25z", fill: "#D0021B", fillRule: "evenodd" }));
});
// CONCATENATED MODULE: ./src/modules/icons/clipboard.svg
var clipboard_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function clipboard_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var clipboard = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = clipboard_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", clipboard_extends({ width: "16", height: "20", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M12 16H4v-2h8v2zm0-6H4v2h8v-2zm2-9h-2v2h2v15H2V3h2V1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm-4 2V2a2 2 0 1 0-4 0v1a2 2 0 0 0-2 2v1h8V5a2 2 0 0 0-2-2z" }));
});
// CONCATENATED MODULE: ./src/modules/icons/cog.svg
var cog_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function cog_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var cog = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = cog_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", cog_extends({ width: "20", height: "20", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M17.867 10c0-.568-.059-1.122-.17-1.656L19.5 6.732l-1.967-3.464-2.283.786a7.813 7.813 0 0 0-2.813-1.657L11.967 0H8.033l-.472 2.396c-1.043.348-2 .913-2.81 1.657l-2.284-.785L.5 6.732l1.804 1.612a8.054 8.054 0 0 0 0 3.312L.5 13.268l1.967 3.464 2.283-.786a7.813 7.813 0 0 0 2.813 1.657L8.033 20h3.934l.472-2.396a7.83 7.83 0 0 0 2.81-1.657l2.284.786 1.967-3.464-1.804-1.613c.112-.535.171-1.09.171-1.657V10zM10 14c-2.173 0-3.934-1.79-3.934-4S7.826 6 10 6c2.173 0 3.934 1.79 3.934 4s-1.76 4-3.934 4z", fill: "#191E23" }));
});
// CONCATENATED MODULE: ./src/modules/icons/info.svg
var info_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function info_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var info = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = info_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", info_extends({ width: "20", height: "20", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M11 7H9V5h2v2zm0 2H9v6h2V9zm-1-7c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm0-2c5.523 0 10 4.477 10 10s-4.477 10-10 10S0 15.523 0 10 4.477 0 10 0z" }));
});
// CONCATENATED MODULE: ./src/modules/icons/pencil.svg
var pencil_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function pencil_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var pencil = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = pencil_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", pencil_extends({ width: "18", height: "18", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M17.254 2.483L15.282.51C14.942.17 14.5 0 14.023 0c-.476 0-.918.17-1.258.51L1.543 11.767c-.034.034-.034.034-.034.068 0 0 0 .034-.034.034-.034.034-.034.034-.034.068v.034c0 .034 0 .034-.034.034L.012 17.14a.57.57 0 0 0 .136.51c.102.102.238.17.374.17.034 0 .102 0 .136-.034l5.136-1.428c.034 0 .034 0 .034-.034h.034c.034 0 .034-.034.068-.034 0 0 .034 0 .034-.034.034-.034.034-.034.068-.034L17.254 4.999c.68-.68.68-1.836 0-2.516zM2.461 16.188l-.884-.885.578-2.176 2.448 2.448-2.142.613zm3.197-1.089l-1.123-1.122-.748-.748-1.122-1.122 9.522-9.522 1.122 1.122.748.748 1.123 1.122L5.658 15.1zM16.506 4.251l-.612.612L12.9 1.87l.612-.612a.692.692 0 0 1 .51-.204c.204 0 .374.068.51.204l1.973 1.973c.272.306.272.748 0 1.02z", fill: "#8D949B" }));
});
// CONCATENATED MODULE: ./src/modules/icons/tag.svg
var tag_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function tag_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var tag = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = tag_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", tag_extends({ width: "20", height: "20", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M18 .007h-7.087c-.53 0-1.04.21-1.414.586L.592 9.5a2 2 0 0 0 0 2.827l7.086 7.086a2 2 0 0 0 2.827 0l8.906-8.906c.376-.374.587-.883.587-1.413V2.007a2 2 0 0 0-2-2H18zM15.007 7a2 2 0 1 1-.09-3.999A2 2 0 0 1 15.007 7z", fill: "#23282D" }));
});
// CONCATENATED MODULE: ./src/modules/icons/user.svg
var user_extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }return target;
};

function user_objectWithoutProperties(obj, keys) {
  var target = {};for (var i in obj) {
    if (keys.indexOf(i) >= 0) continue;if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;target[i] = obj[i];
  }return target;
}


/* harmony default export */ var user = (function (_ref) {
  var _ref$styles = _ref.styles,
      styles = _ref$styles === undefined ? {} : _ref$styles,
      props = user_objectWithoutProperties(_ref, ["styles"]);

  return external_React_default.a.createElement("svg", user_extends({ width: "16", height: "16", xmlns: "http://www.w3.org/2000/svg" }, props), external_React_default.a.createElement("path", { d: "M8 0c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 16s8 0 8-2c0-2.4-3.9-5-8-5s-8 2.6-8 5c0 2 8 2 8 2z" }));
});
// CONCATENATED MODULE: ./src/modules/icons/index.js
/* concated harmony reexport TEC */__webpack_require__.d(__webpack_exports__, "TEC", function() { return tec; });
/* concated harmony reexport Close */__webpack_require__.d(__webpack_exports__, "Close", function() { return icons_close; });
/* concated harmony reexport Alert */__webpack_require__.d(__webpack_exports__, "Alert", function() { return icons_alert; });
/* concated harmony reexport Clipboard */__webpack_require__.d(__webpack_exports__, "Clipboard", function() { return clipboard; });
/* concated harmony reexport Cog */__webpack_require__.d(__webpack_exports__, "Cog", function() { return cog; });
/* concated harmony reexport Info */__webpack_require__.d(__webpack_exports__, "Info", function() { return info; });
/* concated harmony reexport Pencil */__webpack_require__.d(__webpack_exports__, "Pencil", function() { return pencil; });
/* concated harmony reexport Tag */__webpack_require__.d(__webpack_exports__, "Tag", function() { return tag; });
/* concated harmony reexport User */__webpack_require__.d(__webpack_exports__, "User", function() { return user; });










/***/ })

/******/ });