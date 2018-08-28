<style lang="less">
@import 'course_type.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col>
                <Card>
                    <p slot="title" class="ivu-icon ivu-icon-network">
                        分类管理
                    </p>
                    <Row style="margin-top:15px;">
                        <Button type="info" @click="courseTypeAdd">添加分类</Button>
                    </Row>
                    <Row style="margin-top:15px;" id="typeTree">
                        <Table highlight-row stripe border ref="selection" :columns="columns" :data="data"  @on-selection-change='selectionClick'></Table>
                    </Row>
                    <!--<Page :current="current" :page-size="pageSize" :total="total" @on-change="handlePageChange"-->
                          <!--show-elevator show-total></Page>-->
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import axios from 'axios';
    import Cookies from 'js-cookie';

    export default {
        data: function () {
            let that = this;
            return {
                columns: [
                    {
                        title: 'ID',
                        key: 'id'
                    },
                    {
                        title: '名称',
                        key: 'course_type',
                        render: function (h, params) {
                            // console.log(params.row.course_type.replace('&amp;', '&'));
                            // return h('span', {}, params.row.course_type.replace('&amp;', '&'));
                            return h('span', {}, params.row.course_type.replace('&amp;', '&'));
                        }
                    },
                    {
                        title: '描述',
                        key: 'remark'
                    },
                    {
                        title: '来源',
                        key: 'center_name'
                    },
                    {
                        title: '更新时间',
                        key: 'update_time',
                        render: function (h, params) {
                            return h('span', {}, that.dateFormat(params.row.update_time));
                        }
                    },
                    {
                        title: '状态',
                        key: 'status',
                        render: function (h, params) {
                            return h('span', {}, params.row.status === 1 ? '显示' : '隐藏');
                        }
                    },
                    {
                        title: '操作',
                        key: 'action',
                        width: 240,
                        align: 'center',
                        render: (h, params) => {
                            let that = this;
                            if (params.row.center_id.toString() === this.cid) {
                                return h('div', [
                                    h('Button', {
                                        props: {
                                            type: 'primary',
                                            size: 'small'
                                        },
                                        style: {
                                            marginRight: '5px'
                                        },
                                        on: {
                                            click: () => {
                                                that.$router.push({name: 'course_type_edit', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '编辑'),
                                    h('Button', {
                                        props: {
                                            type: 'primary',
                                            size: 'small'
                                        },
                                        style: {
                                            marginRight: '5px'
                                        },
                                        on: {
                                            click: () => {
                                                this.$router.push({name: 'course_type_add', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '添加子分类'),
                                    h('Poptip',
                                        {
                                            props: {
                                                confirm: true,
                                                title: '确定要删除吗！'
                                            },
                                            on: {
                                                'on-ok': function () {
                                                    that.delType(params.row.id);
                                                }
                                            }
                                        },
                                        [h('Button', {
                                            props: {
                                                type: 'error',
                                                size: 'small'
                                            }
                                        }, '删除')]
                                    )
                                ]);
                            } else {
                                return h('div', [
                                    h('Button', {
                                        props: {
                                            type: 'primary',
                                            size: 'small'
                                        },
                                        style: {
                                            marginRight: '5px'
                                        },
                                        on: {
                                            click: () => {
                                                that.$router.push({name: 'course_type_edit', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '查看')
                                ]);
                            }
                        }
                    }
                ],
                data: [],
                source: 0,
                uploder_id: 0,
                create_time: 0,
                type: 0,
                title: '',
                ids: [],
                current: 1,
                pageSize: 10,
                total: 0,
                typeList: {
                    1: '后台',
                    2: '老师'
                },
                sourceList: {},
                uploderList: {},
                dataList: {},
                cid: 0
            };
        },
        created: function () {
            this.cid = Cookies.get('center_id');
            this.getTypeTree();
        },
        methods: {
            getTypeTree: function () {
                var that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/course_type/getTableTree',
                                user_type: 3,
                                all: 1
                            }
                    }
                ).then(function (response) {
                    let dat = response.data;
                    if (dat.status === 1) {
                        that.data = dat.data;
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            handlePageChange: function (cur) {
                this.current = cur;
                this.getvideoList();
            },
            Search: function () {
                this.getvideoList();
            },
            Empty: function () {
                this.type = 0;
                this.source = 0;
                this.create_time = 0;
                this.uploder_id = 0;
                this.title = '';
            },
            change_type: function () {
                if (this.type === '1') {
                    // 后台
                    this.getCenterList(1);
                } else {
                    this.getTeacherList(1);
                }
            },
            delType: function (id) {
                let that = this;
                console.log(id);
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/course_type/delete',
                                user_type: 3,
                                id: id
                            }
                    }
                ).then(function (response) {
                    console.log(response);
                    if (response.data.status === 1) {
                        that.$Message.success('删除成功');
                        that.getTypeTree();
                    } else {
                        that.$Message.error(response.data.msg);
                    }
                }).catch(function (err) {
                    // console.log(err.stack);
                    // that.$Message.error();
                });
            },
            // deleterAll: function () {
            //     if (this.ids.length > 0) {
            //         this.delType();
            //         this.ids = [];
            //     } else {
            //         this.$Message.info('请至少选择一项');
            //     }
            // },
            selectionClick: function (arr) {
                var that = this;
                var ids = [];
                for (var i = 0; i < arr.length; i++) {
                    ids.push(arr[i]['id']);
                }
                that.ids = ids;
            },
            courseTypeAdd: function () {
                this.$router.push({name: 'course_type_add', params: {id: 0}});
            }
        }
    };
</script>
