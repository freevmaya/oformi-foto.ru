var lesson = {
    menu: [
                {
                    id: 1, name: 'Как создать новое дерево', lesson:
                    [
                        {control: '.menu-left .flg', text: 'Нажмите на этой иконке', delay: 800},
                        {control: '.menu-left .submenu', text: 'Нажмите на плюсике', delay: 800},
                        {control: '.dlg_new_tree > .buttons', text: 'Введите необходиные данные и нажмите "Применить"', delay: 100, noarrow: 1}
                    ]
                },{
                    id: 2, name: 'Как удалить дерево', lesson:
                    [
                        {control: '.menu-right .edit', text: 'Перейдите в режим редактирования', 'if': "app.tree.getMode()==MODE_VIEW"},
                        {control: '.edit_panel .btn-trash', text: 'Нажмите на кнопку "Удалить", внизу', delay: 500, noarrow: 1},
                        {control: '.dlg_alert > .buttons', text: 'Прочитайте и нажмите "Ok"', delay: 100}
                    ],
                    check: 'app.isEditPermission()'
                },
                {
                    id: 3, name: 'Как добавить персону', lesson:
                    [
                        {control: '.menu-right .edit', text: 'Перейдите в режим редактирования', 'if': "app.tree.getMode()==MODE_VIEW"},
                        {control: '.edit_panel .btn-add', text: 'Добавьте новую персону', delay: 1200},
                        {control: '.new_persone > .buttons', text: 'Введите необходиные данные и нажмите "Применить"', delay: 1000, noarrow: 1},
                        {control: '.persona', text: 'Наведите курсор на виньетку персоны', delay: 100, event: 'mouseover'}
                    ],
                    check: 'app.isEditPermission()'
                },
                {
                    id: 4, name: 'Как добавить потомка', lesson:
                    [
                        {control: '.menu-right .edit', text: 'Перейдите в режим редактирования', delay: 400, 'if': "app.tree.getMode()==MODE_VIEW"},
                        {control: '.persona', text: 'Наведите курсор на виньетку персоны', delay: 200, event: 'mouseover'},
                        {control: '.uv_buttons .add-child', text: 'Жмите на плюсик, что бы добавить потомка', delay: 100}
                    ],
                    check: 'app.isEditPermission()'
                },
                {
                    id: 5, name: 'Как добавить родителя', lesson:
                    [
                        {control: '.menu-right .edit', text: 'Перейдите в режим редактирования', delay: 400, 'if': "app.tree.getMode()==MODE_VIEW"},
                        {control: '.persona', text: 'Наведите курсор на виньетку персоны', delay: 200, event: 'mouseover'},
                        {control: '.uv_buttons .add-parent', text: 'Жмите на плюсик, что бы добавить нового родителя', delay: 100}
                    ],
                    check: 'app.isEditPermission()'
                },
                {
                    id: 6, name: 'Как присоединить персону', lesson:
                    [
                        {control: '.menu-right .edit', text: 'Перейдите в режим редактирования', 'if': "app.tree.getMode()==MODE_VIEW"},
                        {control: '.pl_list .item', text: 'Зажмите клавишу мыши и перетащите персону на виньетку', delay: 500, event: 'mousedown'},
                        {control: '.persona', text: 'Перетащите на нужную виньетку родственника', delay: 100, event: 'mouseover'},
                    ],
                    check: 'app.isEditPermission()'
                },
                {
                    id: 7, name: 'Загрузить демо-дерево', method: 'loadDemoTree'
                }
           ],
    startTimeline: [
                {control: '.new_persone > .buttons', text: 'Введите необходиные данные и нажмите "Применить"', delay: 1000, noarrow: 1},
                {control: '.menu-right .edit', text: 'Перейдите в режим редактирования', 'if': "app.tree.getMode()==MODE_VIEW"},
                {control: '.persona', text: 'Наведите курсор на виньетку персоны', delay: 600, event: 'mouseover'},
                {control: '.uv_buttons .add-parent', text: 'Жмите на плюсик, что бы добавить нового родителя', delay: 500},
                {control: '.dialog > .buttons', text: 'Введите необходиные данные и нажмите "Применить"', delay: 1000, noarrow: 1},
                {control: '.menu-right .lesson', text: 'Здесь вы найдете еще другие обучающие подсказки', delay: 300}
            ],
    startTimeline2: [
                {control: '.menu-right .lesson', text: 'Здесь вы найдете обучающие подсказки', delay: 300}
            ]
} 