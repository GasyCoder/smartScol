export default function NumberSpinner(elm, opt) {
    var plus = document.querySelectorAll("[data-number='plus']");
    var minus = document.querySelectorAll("[data-number='minus']");
    var number_spinner = document.querySelectorAll(".number-spinner");

    plus.forEach(function (item, index, arr) {
        var parent = plus[index].parentNode;
        plus[index].addEventListener("click", function () {
            var child = plus[index].parentNode.children;
            child.forEach(function (item, index, arr) {
                if (child[index].classList.contains("number-spinner")) {
                    var value = (!child[index].value == "") ? parseInt(child[index].value) : 0;
                    var step = (!child[index].step == "") ? parseInt(child[index].step) : 1;
                    var max = (!child[index].max == "") ? parseInt(child[index].max) : Infinity;
                    if (max + 1 > (value + step)) {
                        child[index].value = value + step;
                    } else {
                        child[index].value = value;
                    }
                }
            });
        })
    });

    minus.forEach(function (item, index, arr) {
        var parent = minus[index].parentNode;
        minus[index].addEventListener("click", function () {
            var child = minus[index].parentNode.children;
            child.forEach(function (item, index, arr) {
                if (child[index].classList.contains("number-spinner")) {
                    var value = (!child[index].value == "") ? parseInt(child[index].value) : 0;
                    var step = (!child[index].step == "") ? parseInt(child[index].step) : 1;
                    var min = (!child[index].min == "") ? parseInt(child[index].min) : 0;
                    if (min - 1 < (value - step)) {
                        child[index].value = value - step;
                    } else {
                        child[index].value = value;
                    }
                }
            });
        })
    });

}