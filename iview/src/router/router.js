import Main from '@/views/Main.vue';

// 不作为Main组件的子页面展示的页面单独写，如下
export const loginRouter = {
    path: '/login',
    name: 'login',
    meta: {
        title: 'Login - 登录'
    },
    component: () => import('@/views/login.vue')
};

export const page404 = {
    path: '/*',
    name: 'error-404',
    meta: {
        title: '404-页面不存在'
    },
    component: () => import('@/views/error-page/404.vue')
};

export const page403 = {
    path: '/403',
    meta: {
        title: '403-权限不足'
    },
    name: 'error-403',
    component: () => import('@//views/error-page/403.vue')
};

export const page500 = {
    path: '/500',
    meta: {
        title: '500-服务端错误'
    },
    name: 'error-500',
    component: () => import('@/views/error-page/500.vue')
};

export const preview = {
    path: '/preview',
    name: 'preview',
    component: () => import('@/views/form/article-publish/preview.vue')
};

export const locking = {
    path: '/locking',
    name: 'locking',
    component: () => import('@/views/main-components/lockscreen/components/locking-page.vue')
};

// 作为Main组件的子页面展示但是不在左侧菜单显示的路由写在otherRouter里
export const otherRouter = {
    path: '/',
    name: 'otherRouter',
    redirect: '/home',
    component: Main,
    children: [
        { path: 'home', title: {i18n: 'home'}, name: 'home_index', component: () => import('@/views/home/home.vue') },
        { path: 'ownspace', title: '个人中心', name: 'ownspace_index', component: () => import('@/views/own-space/own-space.vue') },
        { path: 'order/:order_id', title: '订单详情', name: 'order-info', component: () => import('@/views/advanced-router/component/order-info.vue') }, // 用于展示动态路由
        { path: 'shopping', title: '购物详情', name: 'shopping', component: () => import('@/views/advanced-router/component/shopping-info.vue') }, // 用于展示带参路由
        { path: 'message', title: '消息中心', name: 'message_index', component: () => import('@/views/message/message.vue') },
        { path: 'course/edit/:course_id', title: '慕课编辑', name: 'course_edit', component: () => import('@/views/course/course-edit.vue') },
        { path: 'course/add', title: '慕课添加', name: 'course_add', component: () => import('@/views/course/course-add.vue') },
        { path: 'course/view/:course_id', title: '慕课查看', name: 'course_view', component: () => import('@/views/course/course-view.vue') },
        { path: 'chapter/edit/:course_id', title: '章节编辑', name: 'chapter_edit', component: () => import('@/views/course/chapter-edit.vue') },
        { path: 'video/edit/:video_id', title: '视频编辑', name: 'video_edit', component: () => import('@/views/video/video-edit.vue') },
        { path: 'video/view/:video_id', title: '视频查看', name: 'video_view', component: () => import('@/views/video/video-view.vue') },
        { path: 'video/add/', title: '视频添加', name: 'video_add', component: () => import('@/views/video/video-add.vue') },
        { path: 'course_type/add/:id', title: '分类添加', name: 'course_type_add', component: () => import('@/views/course_type/course_type-add.vue') },
        { path: 'course_type/edit/:id', title: '分类编辑', name: 'course_type_edit', component: () => import('@/views/course_type/course_type-edit.vue') },
        { path: 'column/add/:id', title: '栏目添加', name: 'column_add', component: () => import('@/views/column/column-add.vue') },
        { path: 'column/edit/:id', title: '栏目编辑', name: 'column_edit', component: () => import('@/views/column/column-edit.vue') },
        { path: 'column/column_course_add/:id', title: '栏目课程添加', name: 'column_course_add', component: () => import('@/views/column/column_course-add.vue') },
        { path: 'special_subject/add', title: '专题添加', name: 'spe_sub_add', component: () => import('@/views/special_subject/special_subject-add.vue') },
        { path: 'special_subject/eidt:id', title: '专题添加', name: 'spe_sub_edit', component: () => import('@/views/special_subject/special_subject-edit.vue') },
        { path: 'special_subject/spe_course_add/:id', title: '专题课程添加', name: 'spe_course_add', component: () => import('@/views/special_subject/spe_course-add.vue') },
        { path: 'center/edit/:id', title: '场馆编辑', name: 'center_edit', component: () => import('@/views/center/edit.vue') },
        { path: 'center/add', title: '场馆添加', name: 'center_add', component: () => import('@/views/center/add.vue') },
        { path: 'user/edit/:id', title: '用户编辑', name: 'user_edit', component: () => import('@/views/user/edit.vue') },
        { path: 'user/add', title: '用户添加', name: 'user_add', component: () => import('@/views/user/add.vue')},
        { path: 'course_detail/:id', title: '课程统计详情', name: 'course_detail', component: () => import('@/views/statistics/course-detail.vue') },
        { path: 'course_detail/:id', title: '教学统计详情', name: 'teacher-detail', component: () => import('@/views/statistics/teacher-detail.vue')},
    ]
};

// 作为Main组件的子页面展示并且在左侧菜单显示的路由写在appRouter里
export const appRouter = [
    {
        path: '/course',
        icon: 'ios-book-outline',
        title: '慕课库管理',
        name: '慕课库管理',
        component: Main,
        children: [
            { path: 'index', title: '慕课库管理', name: 'course_index', component: () => import('@/views/course/course.vue') }
        ]
    },
    {
        path: '/video',
        icon: 'videocamera',
        title: '视频库管理',
        name: '视频库管理',
        component: Main,
        children: [
            { path: 'index', title: '视频库管理', name: 'video_index', component: () => import('@/views/video/video.vue') }
        ]
    },
    {
        path: '/course_type',
        icon: 'social-buffer',
        title: '类别管理',
        name: '类别管理',
        component: Main,
        children: [
            { path: 'index', title: '类别管理', name: 'course_type_index', component: () => import('@/views/course_type/course_type.vue') }
        ]
    },
    {
        path: '/column',
        icon: 'ios-paper-outline',
        title: '栏目管理',
        name: '栏目管理',
        component: Main,
        children: [
            { path: 'index', title: '栏目管理', name: 'column_index', component: () => import('@/views/column/column.vue') }
        ]
    },
    {
        path: '/special_subject',
        icon: 'clipboard',
        title: '专题管理',
        name: '专题管理',
        component: Main,
        children: [
            { path: 'index', title: '专题管理', name: 'special_subject_index', component: () => import('@/views/special_subject/special_subject.vue') }
        ]
    },
    {
        path: '/center',
        icon: 'earth',
        title: '文化馆管理',
        name: '文化馆管理',
        component: Main,
        children: [
            { path: 'index', title: '文化馆管理', name: 'center_index', component: () => import('@/views/center/index.vue') },
            { path: 'distribution', title: '资源分配', name: 'distribution_index', component: () => import('@/views/center/distribution.vue') }
        ]
    },
    {
        path: '/user',
        icon: 'person-stalker',
        title: '用户管理',
        name: '用户管理',
        component: Main,
        children: [
            { path: 'index', title: '用户管理', name: 'user_index', component: () => import('@/views/user/user.vue') }
        ]
    },
    {
        path: '/statistics',
        icon: 'earth',
        title: '统计分析',
        name: '统计分析',
        component: Main,
        children: [
            { path: 'index', title: '统计分析', name: 'statistics_index', component: () => import('@/views/statistics/index.vue') },
            { path: 'course', title: '课程统计', name: 'statistics_course', component: () => import('@/views/statistics/course.vue') },
            { path: 'teach', title: '教学统计', name: 'statistics_teach', component: () => import('@/views/statistics/teacher.vue') },
            { path: 'data', title: '数据分析', name: 'statistics_data', component: () => import('@/views/statistics/analysis.vue') }
        ]
    },
    // {
    //     path: '/access',
    //     icon: 'key',
    //     name: 'access',
    //     title: '权限管理',
    //     component: Main,
    //     children: [
    //         { path: 'index', title: '权限管理', name: 'access_index', component: () => import('@/views/access/access.vue') }
    //     ]
    // },
    // {
    //     path: '/access-test',
    //     icon: 'lock-combination',
    //     title: '权限测试页',
    //     name: 'accesstest',
    //     access: 0,
    //     component: Main,
    //     children: [
    //         { path: 'index', title: '权限测试页', name: 'accesstest_index', access: 0, component: () => import('@/views/access/access-test.vue') }
    //     ]
    // },
    // {
    //     path: '/international',
    //     icon: 'earth',
    //     title: {i18n: 'international'},
    //     name: 'international',
    //     component: Main,
    //     children: [
    //         { path: 'index', title: {i18n: 'international'}, name: 'international_index', component: () => import('@/views/international/international.vue') }
    //     ]
    // },
    // {
    //     path: '/component',
    //     icon: 'social-buffer',
    //     name: 'component',
    //     title: '组件',
    //     component: Main,
    //     children: [
    //         {
    //             path: 'text-editor',
    //             icon: 'compose',
    //             name: 'text-editor',
    //             title: '富文本编辑器',
    //             component: () => import('@/views/my-components/text-editor/text-editor.vue')
    //         },
    //         {
    //             path: 'md-editor',
    //             icon: 'pound',
    //             name: 'md-editor',
    //             title: 'Markdown编辑器',
    //             component: () => import('@/views/my-components/markdown-editor/markdown-editor.vue')
    //         },
    //         {
    //             path: 'image-editor',
    //             icon: 'crop',
    //             name: 'image-editor',
    //             title: '图片预览编辑',
    //             component: () => import('@/views/my-components/image-editor/image-editor.vue')
    //         },
    //         {
    //             path: 'draggable-list',
    //             icon: 'arrow-move',
    //             name: 'draggable-list',
    //             title: '可拖拽列表',
    //             component: () => import('@/views/my-components/draggable-list/draggable-list.vue')
    //         },
    //         {
    //             path: 'area-linkage',
    //             icon: 'ios-more',
    //             name: 'area-linkage',
    //             title: '城市级联',
    //             component: () => import('@/views/my-components/area-linkage/area-linkage.vue')
    //         },
    //         {
    //             path: 'file-upload',
    //             icon: 'android-upload',
    //             name: 'file-upload',
    //             title: '文件上传',
    //             component: () => import('@/views/my-components/file-upload/file-upload.vue')
    //         },
    //         {
    //             path: 'scroll-bar',
    //             icon: 'android-upload',
    //             name: 'scroll-bar',
    //             title: '滚动条',
    //             component: () => import('@/views/my-components/scroll-bar/scroll-bar-page.vue')
    //         },
    //         {
    //             path: 'count-to',
    //             icon: 'arrow-graph-up-right',
    //             name: 'count-to',
    //             title: '数字渐变',
    //             // component: () => import('@/views/my-components/count-to/count-to.vue')
    //             component: () => import('@/views/my-components/count-to/count-to.vue')
    //         },
    //         {
    //             path: 'split-pane-page',
    //             icon: 'ios-pause',
    //             name: 'split-pane-page',
    //             title: 'split-pane',
    //             component: () => import('@/views/my-components/split-pane/split-pane-page.vue')
    //         }
    //     ]
    // },
    // {
    //     path: '/form',
    //     icon: 'android-checkbox',
    //     name: 'form',
    //     title: '表单编辑',
    //     component: Main,
    //     children: [
    //         { path: 'artical-publish', title: '文章发布', name: 'artical-publish', icon: 'compose', component: () => import('@/views/form/article-publish/article-publish.vue') },
    //         { path: 'workflow', title: '工作流', name: 'workflow', icon: 'arrow-swap', component: () => import('@/views/form/work-flow/work-flow.vue') }
    //
    //     ]
    // },
    // // {
    // //     path: '/charts',
    // //     icon: 'ios-analytics',
    // //     name: 'charts',
    // //     title: '图表',
    // //     component: Main,
    // //     children: [
    // //         { path: 'pie', title: '饼状图', name: 'pie', icon: 'ios-pie', component: resolve => { require('@/views/access/access.vue') },
    // //         { path: 'histogram', title: '柱状图', name: 'histogram', icon: 'stats-bars', component: resolve => { require('@/views/access/access.vue') }
    //
    // //     ]
    // // },
    // {
    //     path: '/tables',
    //     icon: 'ios-grid-view',
    //     name: 'tables',
    //     title: '表格',
    //     component: Main,
    //     children: [
    //         { path: 'dragableTable', title: '可拖拽排序', name: 'dragable-table', icon: 'arrow-move', component: () => import('@/views/tables/dragable-table.vue') },
    //         { path: 'editableTable', title: '可编辑表格', name: 'editable-table', icon: 'edit', component: () => import('@/views/tables/editable-table.vue') },
    //         { path: 'searchableTable', title: '可搜索表格', name: 'searchable-table', icon: 'search', component: () => import('@/views/tables/searchable-table.vue') },
    //         { path: 'exportableTable', title: '表格导出数据', name: 'exportable-table', icon: 'code-download', component: () => import('@/views/tables/exportable-table.vue') },
    //         { path: 'table2image', title: '表格转图片', name: 'table-to-image', icon: 'images', component: () => import('@/views/tables/table-to-image.vue') }
    //     ]
    // },
    // {
    //     path: '/advanced-router',
    //     icon: 'ios-infinite',
    //     name: 'advanced-router',
    //     title: '高级路由',
    //     component: Main,
    //     children: [
    //         { path: 'mutative-router', title: '动态路由', name: 'mutative-router', icon: 'link', component: () => import('@/views/advanced-router/mutative-router.vue') },
    //         { path: 'argument-page', title: '带参页面', name: 'argument-page', icon: 'android-send', component: () => import('@/views/advanced-router/argument-page.vue') }
    //     ]
    // },
    // {
    //     path: '/error-page',
    //     icon: 'android-sad',
    //     title: '错误页面',
    //     name: 'errorpage',
    //     component: Main,
    //     children: [
    //         { path: 'index', title: '错误页面', name: 'errorpage_index', component: () => import('@/views/error-page/error-page.vue') }
    //     ]
    // }
];

// 所有上面定义的路由都要写在下面的routers里
export const routers = [
    loginRouter,
    otherRouter,
    preview,
    locking,
    ...appRouter,
    page500,
    page403,
    page404
];
