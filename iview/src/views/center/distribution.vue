<style lang="less">
    @import '../../styles/common.less';
    @import './distribution.less';
</style>

<template>
    <div>
        <Row>
            <Card id="header">
                <div slot="title">
                    <p style="width: auto;">
                        <Icon type="pound"></Icon>
                        章节管理
                    </p>
                    <!--<p style="width: auto; float: right;height:25px;">-->
                        <!--<Button type="primary" size="small" @click="handleBack">返回课程编辑</Button>-->
                    <!--</p>-->
                    <!--<div style="clear: both"></div>-->
                </div>
            </Card>
        </Row>

        <Row style="background-color: #fff">
            <Col span="10">
                <Card>
                    <p slot="title">
                        <Icon type="android-menu"></Icon>
                        慕课分配
                    </p>
                    <Form id="form-data">
                        <FormItem label="选择场馆:" :label-width="80">
                            <Select v-model="center_id">
                                <Option v-for="(name, id) in centers" :value="id" :key="id">{{name}}</Option>
                            </Select>
                        </FormItem>
                        <Tree :data="data"></Tree>
                        <Row style='text-align: right;'>
                            <Button type="primary" @click="saveData">保存</Button>
                        </Row>
                    </Form>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    // import tinymce from 'tinymce';
    import jq from 'jquery';

    export default {
        name: 'distribution',
        data: function () {
            return {
                // 根节点构建
                centers: {},
                center_id: 0,
                data: [
                    {
                        title: '超星文化馆',
                        expand: true,
                        render: (h, {root, node, data}) => {
                            return h('span', {
                                style: {
                                    display: 'inline-block',
                                    width: '100%'
                                }
                            }, [
                                h('span', [
                                    h('Icon', {
                                        props: {
                                            type: 'merge'
                                        },
                                        style: {
                                            marginRight: '8px'
                                        }
                                    }),
                                    h('span', data.title)
                                ])
                            ]);
                        },
                        children: []
                    }
                ],
                buttonProps: {
                    type: 'ghost',
                    size: 'small'
                }
            };
        },
        methods: {
            saveData: function () {
                // 保存数据
                let ids = {};
                jq('[name^=ids]').each(function (i, item) {
                    let num = item.value;
                    if (num > 0) {
                        let id = item.getAttribute('name').split('-').pop();
                        ids[id] = num;
                    }
                });
                let that = this;
                this.request({
                    api: '/v1/initialize/distribute',
                    user_type: 3,
                    center_id: this.center_id,
                    ids: ids
                }, function (response) {
                    if (response.status === 1) {
                        that.$Message.success('初始化完成');
                    } else {
                        that.$Message.error(response.msg);
                    }
                });
            },
            getTree: function () {
                let that = this;
                this.request({
                    api: '/v1/initialize/index',
                    user_type: 3
                }, function (response) {
                    if (response.status === 1) {
                        that.data[0]['children'] = convertTree(response.data);
                    } else {
                        that.$Notice.error(response.msg);
                    }
                });
            },
            getCenter: function () {
                let that = this;
                this.request({
                    api: '/v1/mooc_center/index',
                    user_type: 3
                }, function (response) {
                    if (response.status === 1) {
                        let centers = {};
                        for (let i in response.data) {
                            if (response.data[i]['id'] !== 1) {
                                centers[response.data[i]['id'].toString()] = response.data[i]['center_name'];
                            }
                        }
                        that.centers = centers;
                    }
                });
            }
        },
        mounted () {
            // 初始化
            this.id = this.$route.params.course_id;
            this.getCenter();
            this.getTree();
            // this.upload_token = this.getUploadData();
        }
    };

    /**
     * tree 数据转换
     * @param  {Array} tree 待转换的 tree
     * @return {Array}      转换后的 tree
     */
    function convertTree (tree) {
        const result = [];

        for (let i in tree) {
            let item = tree[i];
            // 解构赋值
            let {
                id: value,
                course_type: label,
                children,
                num
            } = item;

            // 如果有子节点，递归
            if (children) {
                children = convertTree(children);
                let obj = {
                    value,
                    label,
                    children,
                    num,
                    expand: true,
                    title: label
                };
                if (children.length === 0) {
                    obj['render'] = function (h, {root, node, data}) {
                        return h('span', {style: {width: '100%',
                            display: 'inline-block'}}, [
                            h('span', {style: {lineHeight: '30px'}}, data.title),
                            h('Input', {props: {value: data.num}, attrs: {name: 'ids-' + data.value + '', value: data.num}, style: {width: '80px', float: 'right'}}),
                            h('div', {style: {clear: 'both'}})
                        ]);
                    };
                }
                result.push(obj);
            }
        }

        return result;
    }
</script>
