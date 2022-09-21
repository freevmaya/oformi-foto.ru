var relatives = [
    {
        id          : 0,
        dname        : 'Фамил Имен Отецович',
        img: PLIMAGEPATH + 'ya.jpg',
        gender      : MALE,
        bday        : '01.02.1990',
        parentIds   : [1, 2],
        childIds    : [3, 4]
    },{
        id          : 1,
        dname        : 'Фамил Отец Дедушков',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'o01.jpg',
        parentIds   : [7, 8],
        childIds    : [0, 9]
    },{
        id          : 2,
        dname        : 'Фамилия Мама Дедушкова',
        gender      : FEMALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'image_13.jpg',
        parentIds   : [],
        childIds    : [0]
    },{
        id          : 3,
        dname: 'Фамил Сын Именович',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'si01.jpg',
        parentIds   : [0],
        childIds    : [5, 6]
    }, {
        id          : 4,
        gender      : FEMALE,
        bday        : '01.02.1990',
        dname: 'Фамилия Доча Именовична',
        img: PLIMAGEPATH + 'image_07.jpg',
        parentIds   : [0]
    },{
        id          : 5,
        dname: 'Фамил Внук Сынович',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'vn01.jpg',
        parentIds   : [3]
    },{
        id          : 6,
        dname: 'Фамил Внучка Сыновична',
        gender      : FEMALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'v01.jpg',
        parentIds   : [3]
    },
    //-------------------------------
    {
        id          : 7,
        dname        : 'Фамил Дед ПраДедешков',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'd01.jpg',
        parentIds   : [],
        childIds    : [1, 10]
    },{
        id          : 8,
        dname        : 'Фамилия Бабушка ПраДедушкова',
        gender      : FEMALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'b01.jpg',
        parentIds   : [],
        childIds    : [1]
    } ,
    //-------------------------------
    {
        id          : 9,
        dname        : 'Фамил Брат Отцович',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'br01.jpg',
        parentIds   : [1, 2],
        childIds    : []
    }  ,
    //-------------------------------
    {
        id          : 10,
        dname        : 'Фамил БратОтца Дедушков',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'br02.jpg',
        parentIds   : [7, 8],
        childIds    : [11, 12]
    },{
        id          : 11,
        dname: 'Фамил ДвБрат Братоцович',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'br03.jpg',
        parentIds   : [10]
    },{
        id          : 12,
        dname: 'Фамил ДвСетра Братоцовична',
        gender      : FEMALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 's01.jpg',
        parentIds   : [10],
        childIds    : [13]
    },{
        id          : 13,
        dname: 'Фамил ДвПлемянник ДвСестрывич',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'trp01.jpg',
        parentIds   : [12],
        childIds    : [14, 15]
    } ,
    //-------------------------------
    {
        id          : 14,
        dname        : 'Фамил ДвВнук ДвПлемянникович',
        gender      : MALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'def_male.png',
        parentIds   : [13]
    },{
        id          : 15,
        dname: 'Фамил ДвВнук ДвПлемянникович',
        gender      : FEMALE,
        bday        : '01.02.1990',
        img: PLIMAGEPATH + 'def_female.png',
        parentIds   : [13]
    }
]

