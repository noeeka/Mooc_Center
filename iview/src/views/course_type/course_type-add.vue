<style lang="less">
    @import './course_type-add.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <Form :label-width="80">
                        <FormItem label="父分类">
                            <Select  v-model='type_id'>
                                <Option value="0">作为顶级</Option>
                                <Option v-for="(name, id) in type" :value="id" :key="id" >{{ name }}</Option>
                            </Select>
                        </FormItem>
                        <FormItem label="分类名称">
                            <Input v-model="course_type" icon="android-list"/>
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="备注">
                            <textarea name="remark" v-model="remark" id="remark" cols="180" rows="5">
                            </textarea>
                        </FormItem>
                        <FormItem>
                            <Button type="primary" @click="SubmitType">Submit</Button>
                            <Button type="ghost" style="margin-left: 8px" @click="handleBack">Cancel</Button>
                        </FormItem>
                    </Form>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import axios from 'axios';
    // import tinymce from 'tinymce';

    export default {
        name: 'video-edit',
        data: function () {
            return {
                type_id: 0,
                type: {},
                course_type: '',
                remark: '',
                status: '1',
            };
        },
        created: function () {
            this.getTypeList();
        },
        methods: {
            getTypeList: function () {
                let that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/course_type/getTableTree',
                                user_type: 3
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.type = that.array_map(response.data, 'id', 'course_type');
                        that.type_id = that.$route.params.id.toString();
                    } else {
                        that.type = {};
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            SubmitType: function () {
                var that = this;
                if (this.course_type.length === 0) {
                    this.$Message.error('标题不能为空');
                    return false;
                }
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                user_type: 3,
                                api: '/v1/course_type/save',
                                course_type: this.course_type,
                                parent_id: this.type_id,
                                remark: this.remark,
                                status: this.status
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.$Modal.info({
                            title: '添加',
                            content: '添加成功',
                            onOk: function () {
                                that.$router.push({name: 'course_type_index'});
                            }
                        });
                    } else {
                        that.$Message.error(response.msg);
                    }
                }).catch(function (err) {
                    console.log(err.track);
                });
            },
            handleBack: function () {
                this.$router.go(-1);
            },
            handleUpload: function () {

            }
        }
        // mounted () {
        //     tinymce.init({
        //         selector: '#remark',
        //         branding: false,
        //         elementpath: false,
        //         height: 600,
        //         language: 'zh_CN.GB2312',
        //         menubar: 'edit insert view format table tools',
        //         theme: 'modern',
        //         plugins: [
        //             'advlist autolink lists link image charmap print preview hr anchor pagebreak imagetools',
        //             'searchreplace visualblocks visualchars code fullscreen fullpage',
        //             'insertdatetime media nonbreaking save table contextmenu directionality',
        //             'emoticons paste textcolor colorpicker textpattern imagetools codesample'
        //         ],
        //         toolbar1: ' newnote print fullscreen preview | undo redo | insert | styleselect | forecolor backcolor bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image emoticons media codesample',
        //         autosave_interval: '20s',
        //         image_advtab: true,
        //         table_default_styles: {
        //             width: '100%',
        //             borderCollapse: 'collapse'
        //         }
        //     });
        // }
        // destroyed () {
        //     tinymce.get('articleEditor').destroy();
        // }
    };
</script>
