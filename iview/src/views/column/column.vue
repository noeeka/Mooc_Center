<style lang="less">
@import './column.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col>
                <Card>
                    <p slot="title">
                        <Icon type="ios-paper-outline"></Icon>
                        栏目管理
                    </p>
                    <Row style="margin-top:15px;">
                        <Button type="info" @click="columnAdd">添加栏目</Button>
                    </Row>
                    <Row style="margin-top:15px;" id="columnTree">
                        <Table highlight-row stripe border ref="selection" :columns="columns" :data="data"  @on-selection-change='selectionClick'></Table>
                    </Row>
                    <Page :current="current" :page-size="pageSize" :total="total" @on-change="handlePageChange"
                          show-elevator show-total></Page>
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
            return {
                columns: [
                    {
                        title: 'ID',
                        key: 'id'
                    },
                    {
                        title: '名称',
                        key: 'title'
                    },
                    {
                        title: '来源',
                        key: 'center_name'
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
                        width: 300,
                        align: 'center',
                        render: (h, params) => {
                            let that = this;
                            let action = [];
                            if (params.row.center_id.toString() === this.cid && params.row.level.toString() === '2') {
                                action[action.length] = h('Button', {
                                    props: {
                                        type: 'primary',
                                        size: 'small'
                                    },
                                    style: {
                                        marginRight: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            that.$router.push({name: 'column_course_add', params: {id: params.row.id}});
                                        }
                                    }
                                }, '添加课程');
                            }
                            if (params.row.center_id.toString() === this.cid) {
                                action[action.length] = [
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
                                                that.$router.push({name: 'column_add', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '添加子栏目'),
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
                                                that.$router.push({name: 'column_edit', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '编辑'),
                                    h('Poptip',
                                        {
                                            props: {
                                                confirm: true,
                                                title: '确定要删除吗！'
                                            },
                                            on: {
                                                'on-ok': function () {
                                                    that.delColumn(params.row.id);
                                                }
                                            }
                                        },
                                        [h('Button', {
                                            props: {
                                                type: 'error',
                                                size: 'small'
                                            }
                                        }, '删除')]
                                    )];
                            } else {
                                action[action.length] = [
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
                                                that.$router.push({name: 'column_edit', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '查看')
                                ];
                            }
                            return h('div', action);
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
            // this.cid = '14';
            this.cid = Cookies.get('center_id');
            this.getColumnTree();
        },
        methods: {
            getColumnTree: function () {
                var that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/column/getColumnTable',
                                user_type: 3,
                                page: this.current,
                                len: this.pageSize,
                                all: 1
                            }
                    }
                ).then(function (response) {
                    let dat = response.data;
                    if (dat.status === 1) {
                        that.data = dat.data.list;
                        that.total = dat.data.num;
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            handlePageChange: function (cur) {
                this.current = cur;
                this.getColumnTree();
            },
            Search: function () {
                this.getvideoList();
            },
            delColumn: function (id) {
                let that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/column/delete',
                                user_type: 3,
                                id: id
                            }
                    }
                ).then(function (response) {
                    console.log(response);
                    if (response.data.status === 1) {
                        that.$Message.success('删除成功');
                        that.getColumnTree();
                    } else {
                        that.$Message.error(response.data.msg);
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                    // that.$Message.error();
                });
            },
            selectionClick: function (arr) {
                var that = this;
                var ids = [];
                for (var i = 0; i < arr.length; i++) {
                    ids.push(arr[i]['id']);
                }
                that.ids = ids;
            },
            columnAdd: function () {
                this.$router.push({name: 'column_add', params: {id: 0}});
            }
        }
    };
</script>
