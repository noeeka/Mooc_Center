<style lang="less">
@import '../../styles/common.less';
@import './edit.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <p slot="title">
                    <Icon type="edit"></Icon>
                        文化馆编辑
                    </p>
                    <Form :label-width="80">
                        <FormItem label="文化馆名称:" :label-width="80">
                            <Input v-model="center_name"/>
                        </FormItem>
                        <FormItem label="所属地区:" :label-width="80">
                            <Input v-model="address"/>
                        </FormItem>
                        <FormItem label="Access Key:" :label-width="80">
                            <Input v-model="access_key" disabled/>
                        </FormItem>
                        <FormItem label="状态:">
                            <RadioGroup v-model="status">
                                <Radio v-if="id==1"disabled label="1">启用</Radio>
                                <Radio v-else label="1">启用</Radio>
                                <Radio v-if="id==1"disabled label="0">禁用</Radio>
                                <Radio v-else label="0">禁用</Radio>
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
        name: 'center-edit',
        data: function () {
            return {
                id: 0,
                center_name: '',
                address: '',
                access_key: '',
                status: '0'
            };
        },
        mounted: function () {
            this.id = this.$route.params.id;
            this.getData();
        },
        methods: {
            getData: function () {
                let that = this;
                this.request({
                    api: '/v1/mooc_center/read',
                    user_type: 3,
                    id: this.id
                }, function (response) {
                    if (response.status === 1) {
                        that.center_name = response.data.center_name;
                        that.address = response.data.address;
                        that.access_key = response.data.access_key;
                        that.status = response.data.status.toString();
                    } else {
                        that.$Modal.error({content: '场馆不存在',
                            onOk: function () {
                                that.$router.push({name: 'center_index'});
                            }
                        });
                    }
                });
            },
            saveData: function () {
                let that = this;
                if (this.center_name === '') {
                    this.$Message.error('文化馆名称不能为空');
                    return false;
                }
                this.request({
                    api: '/v1/mooc_center/edit',
                    user_type: 3,
                    id: this.id,
                    center_name: this.center_name,
                    address: this.address,
                    status: this.status
                }, function (response) {
                    if (response.status === 1) {
                        that.$Message.success('保存成功');
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
