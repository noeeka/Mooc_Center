<style lang="less">
    @import '../../styles/common.less';
    @import './add.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <p slot="title">
                        <Icon type="edit"></Icon>
                        文化馆新增
                    </p>
                    <Form :label-width="80">
                        <FormItem label="文化馆名称:">
                            <Input v-model="center_name"/>
                        </FormItem>
                        <FormItem label="所属地区:">
                            <Input v-model="address"/>
                        </FormItem>
                        <FormItem label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem>
                            <Button type="ghost" style="margin-left: 8px" @click="handleBack">返回</Button>
                            <Button type="primary" @click="saveData">保存</Button>
                        </FormItem>
                    </Form>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    export default {
        name: 'center-add',
        data: function () {
            return {
                id: 0,
                center_name: '',
                address: '',
                status: '1'
            };
        },
        mounted: function () {
            this.id = this.$route.params.id;
        },
        methods: {
            saveData: function () {
                let that = this;
                if (this.center_name === '') {
                    this.$Message.error('文化馆名称不能为空');
                    return false;
                }
                this.request({
                    api: '/v1/mooc_center/add',
                    user_type: 3,
                    id: this.id,
                    center_name: this.center_name,
                    address: this.address,
                    status: this.status
                }, function (response) {
                    if (response.status === 1) {
                        that.$Modal.confirm({
                            content: '添加成功',
                            okText: '查看详情',
                            cancelText: '返回列表页',
                            onOk: function () {
                                that.$router.push({name: 'center_edit', params: {id: response.data}});
                            },
                            onCancel: function () {
                                that.$router.push({name: 'center_index'});
                            }
                        });
                    } else {
                        that.$Message.error(response.msg);
                    }
                });
            },
            handleBack: function () {
                this.$router.go(-1);
            }
        }
    };
</script>
