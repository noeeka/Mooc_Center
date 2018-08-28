<style lang="less">
    @import './index.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col>
                <Card>
                    <p slot="title">
                        <Icon type="home"></Icon>
                        文化馆管理
                    </p>
                    <Row>
                        <router-link class="ivu-btn ivu-btn-primary" to="/center/add">添加文化馆</router-link>
                    </Row>
                    <Row style="margin-top:15px;">
                        <Col span="15">
                        <Table highlight-row stripe border ref="selection" :columns="columns" :data="data"></Table>
                        </Col>
                    </Row>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import axios from 'axios';
    import jq from 'jquery';

    export default {
        data: function () {
            let that = this;
            return {
                columns: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '文化馆名称',
                        key: 'center_name'
                        // width: 160
                    },
                    {
                        title: '地区',
                        key: 'address'
                        // width: 240
                    },
                    {
                        title: '状态',
                        key: 'status',
                        // width: 80,
                        render: function (h, params) {
                            return h('span', {}, params.row.status === 1 ? '启用' : '禁用');
                        }
                    },
                    {
                        title: '操作',
                        // width: 150,
                        align: 'left',
                        render: (h, params) => {
                            let that = this;
                            if (params.row.id === 1) {
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
                                                that.$router.push({name: 'center_edit', params: {id: params.row.id}});
                                                // this.show(params.index)
                                            }
                                        }
                                    }, '编辑')
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
                                                that.$router.push({name: 'center_edit', params: {id: params.row.id}});
                                                // this.show(params.index)
                                            }
                                        }
                                    }, '编辑'),
                                    h('Button', {
                                        props: {
                                            type: params.row.status === 1 ? 'error' : 'primary',
                                            size: 'small'
                                        },
                                        style: {
                                            marginRight: '5px'
                                        },
                                        attrs: {
                                            id: 'status-' + params.row.id
                                        },
                                        on: {
                                            click: (e) => {
                                                that.changeStatus(params.row.id);
                                            }
                                        }
                                    }, params.row.status === 1 ? '禁用' : '启用')
                                ]);
                            }
                        }
                    }
                ],
                data: []
            };
        },
        mounted: function () {
            this.getCenter();
        },
        methods: {
            changeStatus: function (id) {
                let that = this;
                let status = jq('#status-' + id).text().trim();
                let statusVal = '';
                console.log(status);
                if (status === '启用') {
                    statusVal = 1;
                } else {
                    statusVal = 0;
                }

                console.log(statusVal);
                this.request({
                    api: '/v1/mooc_center/edit',
                    user_type: 3,
                    id: id,
                    status: statusVal
                }, function (response) {
                    if (response.status === 1) {
                        if (statusVal === 0) {
                            jq('#status-' + id).removeClass('ivu-btn-error').addClass('ivu-btn-primary').html('启用');
                            that.$Message.success('启用成功');
                        } else {
                            jq('#status-' + id).removeClass('ivu-btn-primary').addClass('ivu-btn-error').html('禁用');
                            that.$Message.success('禁用成功');
                        }
                    } else {
                        that.$Message.error(response.msg);
                    }
                });
            },
            getCenter: function (type) {
                let that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/mooc_center/index',
                                user_type: 3,
                                all: 1
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.data = response.data;
                    } else {
                        that.sourceList = [];
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            }
        }
    };
</script>
