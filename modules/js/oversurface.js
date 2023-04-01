/** This function "projects" the object on oversurface which is
 invisible plain hovering over the area
 Note: object size and other properties must NOT be set relative
 to parent (or you have to adjust them on oversurface) */
function project(from, postfix) {
    var elem = $(from);
    var over = $("oversurface");
    var par = elem.parentNode;
    var overRect = over.getBoundingClientRect();
    var elemRect = elem.getBoundingClientRect();

    var centerY = elemRect.y + elemRect.height / 2;
    var centerX = elemRect.x + elemRect.width / 2;

    //console.log("elemRect", elemRect);

    var offsetY = 0;
    var offsetX = 0;

    var newId = elem.id + postfix;
    var old = $(newId);
    if (old) old.parentNode.removeChild(old);

    var clone = elem.cloneNode(true);
    clone.id = newId;

    // this caclculates transitive maxtrix for transformations of the parent
    // so we can apply it oversurface to match exact scale and rotate
    var fullmatrix = "";
    while (par != over.parentNode && par != null && par != document) {
        var style = window.getComputedStyle(par);
        var matrix = style.transform; //|| "matrix(1,0,0,1,0,0)";

        if (matrix && matrix != "none") fullmatrix += " " + matrix;
        par = par.parentNode;
        //console.log("tranform  ",fullmatrix,par.id);
    }

    // Doing this now means I can use getBoundingClientRect
    over.appendChild(clone);

    var cloneRect = clone.getBoundingClientRect();

    // centerX/Y is where the center point must be
    // I need to calculate the offset from top and left
    // Therefore I remove half of the dimensions + the existing offset
    var offsetY = centerY - cloneRect.height / 2 - cloneRect.y;
    var offsetX = centerX - cloneRect.width / 2 - cloneRect.x;

    // Finally apply the offects and transform - we should have exact copy of object but on different parent

    clone.style.left = offsetX + "px";
    clone.style.top = offsetY + "px";
    clone.style.transform = fullmatrix;

    return clone;
}

function phantomMove(mobileId, newparentId, duration) {
    var box = $(mobileId);
    var newparent = $(newparentId);
    var clone = project(box.id, "_temp");
    box.style.opacity = 0;
    newparent.appendChild(box);

    var desti = project(box.id, "_temp2");

    clone.style.transitionDuration = duration + "ms";
    //clone.offsetTop;
    clone.style.left = desti.style.left;
    clone.style.top = desti.style.top;
    clone.style.transform = desti.style.transform;
    //console.log(desti.style.top, clone.style.top);
    desti.parentNode.removeChild(desti);
    setTimeout(() => {
        box.style.removeProperty("opacity");
        if (clone.parentNode) clone.parentNode.removeChild(clone);
    }, duration);
}