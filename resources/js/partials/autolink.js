//Forked from : https://github.com/bryanwoods/autolink-js

function getAttributes(options) {
    let results;
    results = [];
    for (let k in options) {
        results.push(" " + k + "='" + options[k] + "'");
    }
    return results.join('');
}

export default function (text, options = {}) {
    let pattern = /(^|[\s\n]|<[A-Za-z]*\/?>)((?:https?|ftp):\/\/[\-A-Z0-9+\u0026\u2019@#\/%?=()~_|!:,.;]*[\-A-Z0-9+\u0026@#\/%=~()_|])/gi;
    if (!(options && Object.keys(options).length)) {
        return text.replace(pattern, "$1<a href='$2'>$2</a>");
    }

    return text.replace(pattern, (match, space, url) => space + ("<a href='" + url + "'" + getAttributes(options) + ">" + url + "</a>"));
};

