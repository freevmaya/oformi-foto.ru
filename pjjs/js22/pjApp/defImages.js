var GPDImages = {
    0: ['images/people/ch01.jpg', 'images/people/ch02.jpg', 'images/people/ch03.jpg', 'images/people/ch04.jpg'],
    44: ['images/letters/l01.jpg'],
}

var textGroups = [44];

var _defaultImages = GPDImages[0];

function isTextGroup(groupId) {
    return textGroups.indexOf(groupId) > -1;
}