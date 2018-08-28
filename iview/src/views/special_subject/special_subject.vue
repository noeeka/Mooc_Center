<style lang="less">
@import './special_subject.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col>
                <Card>
                    <p slot="title">
                        <Icon type="clipboard"></Icon>
                        专题管理
                    </p>
                    <Row style="margin-top:15px;">
                        <Button @click="deleterAll" type="error">批量删除</Button>
                        <Button type="info" @click="speAdd">添加专题</Button>
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
            let that = this;
            return {
                columns: [
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center'
                    },
                    {
                        title: 'ID',
                        key: 'id'
                    },
                    {
                        title: '专题名称',
                        key: 'title'
                    },
                    {
                        title: '专题描述',
                        key: 'remark'
                    },
                    {
                        title: '来源',
                        key: 'center_name'
                    },
                    {
                        title: '创建时间',
                        key: 'create_time',
                        render: function (h, params) {
                            return h('span', {}, that.dateFormat(params.row.create_time));
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
                            if(this.cid === params.row.center_id.toString()){
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
                                                this.$router.push({name: 'spe_course_add', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '添加课程'),
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
                                                that.$router.push({name: 'spe_sub_edit', params: {id: params.row.id}});
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
                                                    that.delSpe(params.row.id);
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
                            }else{
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
                                                that.$router.push({name: 'spe_sub_edit', params: {id: params.row.id}});
                                            }
                                        }
                                    }, '查看'),
                                ]);
                            }

                        }
                    }
                ],
                data: [],
                title: '',
                create_time: 0,
                current: 1,
                pageSize: 10,
                total: 0,
                ids: [],
                cid: 0
            };
        },
        created: function () {
            this.cid = Cookies.get('center_id');
            this.getSpeSubList();
        },
        methods: {
            request: function (params, callback1) {
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {params: params}
                ).then(callback1).catch();
            },
            getSpeSubList: function () {
                var that = this;
                that.request(
                    {
                        api: '/v1/special_subject/index',
                        user_type: 3,
                        page: this.current,
                        len: this.pageSize,
                        all: 1
                    },
                    function (response) {
                        console.log(response);
                        let dat = response.data;
                        if (dat.status === 1) {
                            that.data = dat.data.list;
                            that.total = dat.data.num;
                        }
                    }
                );
            },
            handlePageChange: function (cur) {
                this.current = cur;
                this.getSpeSubList();
            },
            Search: function () {
                this.getvideoList();
            },
            delSpe: function (id) {
                let that = this;
                that.request(
                    {
                        api: '/v1/special_subject/delete',
                        user_type: 3,
                        id: id,
                        ids: this.ids
                    },
                    function (response) {
                        if (response.data.status === 1) {
                            that.$Message.success('删除成功');
                            that.getSpeSubList();
                        } else {
                            that.$Message.error(response.data.msg);
                        }
                    }
                );
            },
            deleterAll: function () {
                if (this.ids.length > 0) {
                    this.delSpe();
                    this.ids = [];
                } else {
                    this.$Message.info('请至少选择一项');
                }
            },
            selectionClick: function (arr) {
                var that = this;
                var ids = [];
                for (var i = 0; i < arr.length; i++) {
                    ids.push(arr[i]['id']);
                }
                that.ids = ids;
                console.log(that.ids);
            },
            speAdd: function () {
                this.$router.push({name: 'spe_sub_add'});
            }
        }
    };
</script>
