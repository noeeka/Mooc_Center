<style lang="less">
@import './user.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col>
                <Card>
                    <p slot="title">
                        <Icon type="person-stalker"></Icon>
                        用户管理
                    </p>
                    <Row>
                        <label for="nick_name">用户</label>
                        <Input v-model="nick_name" id="nick_name" placeholder="" style="width:200px;"/>
                        <label for="type">角色</label>
                        <Select v-model="type" id="type" style="width:200px">
                            <Option value="1">用户</Option>
                            <Option value="2">老师</Option>
                            <Option value="3">超级管理员</Option>
                        </Select>
                        <Button @click="Search" icon="search" type="primary">搜索</Button>
                        <Button @click="Empty" type="ghost">清空</Button>
                    </Row>
                    <Row style="margin-top:15px;">
                        <Button  type="warning" @click="changeAllStatus(0)">禁用</Button>
                        <Button  type="warning" @click="changeAllStatus(1)">启用</Button>
                        <!--<Button  type="error" @click="delAll">删除</Button>-->
                        <Button type="info" @click="userAdd">添加老师</Button>
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
                        title: '用户名',
                        key: 'user_login'
                    },
                    {
                        title: '所属文化馆',
                        key: 'center_name'
                    },
                    {
                        title: '角色',
                        key: 'type',
                        render: function (h, params) {
                            if (params.row.type === 1) {
                                return h('span', {}, '用户');
                            } else if (params.row.type === 2) {
                                return h('span', {}, '老师');
                            } else if (params.row.type === 3) {
                                return h('span', {}, '超级管理员');
                            } else {
                                return h('span', {}, '文化馆管理员');
                            }
                        }
                    },
                    {
                        title: '状态',
                        key: 'status',
                        render: function (h, params) {
                            return h('span', {}, params.row.status === 1 ? '启用' : '禁用');
                        }
                    },
                    {
                        title: '操作',
                        key: 'action',
                        width: 240,
                        align: 'center',
                        render: (h, params) => {
                            let that = this;
                            let action = [];
                            // 只能编辑本馆老师
                            if (params.row.type === 2 && params.row.center_id.toString() === this.cid) {
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
                                            that.$router.push({name: 'user_edit', params: {id: params.row.id}});
                                        }
                                    }
                                }, '编辑');
                            } else {
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
                                            that.$router.push({name: 'user_edit', params: {id: params.row.id}});
                                        }
                                    }
                                }, '查看');
                            }

                            // 只能禁用本馆用户和老师，不能禁用超级管理员
                            if (params.row.center_id.toString() === this.cid && params.row.id !== 1) {
                                action[action.length] = h('Button', {
                                    props: {
                                        type: 'warning',
                                        size: 'small'
                                    },
                                    style: {
                                        marginRight: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            this.changeStatus(params.row.status === 1 ? 0 : 1, params.row.id);
                                        }
                                    }
                                }, params.row.status === 1 ? '禁用' : '启用');
                            }
                            return h('div', action);
                        }
                    }
                ],
                data: [],
                nick_name: '',
                type: [],
                current: 1,
                pageSize: 10,
                total: 0,
                ids: [],
                status: 0,
                cid: 0
            };
        },
        created: function () {
            // this.cid = '14';
            this.cid = Cookies.get('center_id');
            this.getUserList();
        },
        methods: {
            request: function (params, callback1) {
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {params: params}
                ).then(callback1).catch();
            },
            getUserList: function () {
                var that = this;
                that.request(
                    {
                        api: '/v1/user/index',
                        user_type: 3,
                        page: this.current,
                        len: this.pageSize,
                        type: this.type,
                        nick_name: this.nick_name
                    },
                    function (response) {
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
                this.getUserList();
            },
            Search: function () {
                this.getUserList();
            },
            Empty: function () {
                this.nick_name = '';
                this.type = 0;
            },
            // delUser: function (id) {
            //     let that = this;
            //     that.request(
            //         {
            //             api: '/v1/User/delete',
            //             user_type: 3,
            //             id: id,
            //             ids: this.ids
            //         },
            //         function (response) {
            //             if (response.data.status === 1) {
            //                 that.$Message.success('删除成功');
            //                 that.getUserList();
            //             } else {
            //                 that.$Message.error(response.data.msg);
            //             }
            //         }
            //     );
            // },
            selectionClick: function (arr) {
                var that = this;
                var ids = [];
                for (var i = 0; i < arr.length; i++) {
                    ids.push(arr[i]['id']);
                }
                that.ids = ids;
            },
            changeStatus: function (status, id) {
                let that = this;
                that.request(
                    {
                        api: '/v1/user/updateStatus',
                        user_type: 3,
                        id: id,
                        ids: this.ids,
                        status: status
                    },
                    function (response) {
                        console.log(response);
                        if (response.data.status === 1) {
                            that.$Message.success('修改状态成功');
                            that.getUserList();
                        } else {
                            that.$Message.error(response.data.msg);
                        }
                    }
                );
            },
            changeAllStatus: function (status) {
                console.log(this.ids);
                if (this.ids.length > 0) {
                    this.changeStatus(status);
                    this.ids = [];
                } else {
                    this.$Message.info('请至少选择一项');
                }
            },
            // delAll: function () {
            //     if (this.ids.length > 0) {
            //         this.delUser(0);
            //     } else {
            //         this.$Message.info('请至少选择一项');
            //     }
            // },
            userAdd: function () {
                this.$router.push({name: 'user_add'});
            }
        }
    };
</script>
