<?php namespace Phpcmf\Library\Httpapi;

class Function_module {

    // 获取字段
    private function _get_field($name, $module) {
        $field = [];
        if ($module['field'][$name]) {
            $field = $module['field'][$name];
        }
        return $field;
    }

    // Ftable
    public function ftable($name, $value, $module) {

        $field = $this->_get_field($name, $module);
        if (!$field) {
            return $name;
        }

        if (function_exists('dr_get_ftable_array')) {
            return dr_get_ftable_array($field['id'], $value);
        }

        $value = dr_string2array($value);
        if ($value) {
            $img = [];
            foreach ($field['setting']['option']['field'] as $n => $t) {
                if ($t['type']) {
                    if ($t['type'] == 3) {
                        // 图片
                        $img[] = $n;
                    }
                }
            }
            $rt = [];
            foreach ($value as $row) {
                if ($img) {
                    foreach ($img as $i) {
                        $row[$i] = dr_get_file($row[$i]);
                    }
                }
                $rt[] = $row;
            }
            return $rt;
        }


        return $value;
    }

    // 单选字段name
    public function radio_name($name, $value, $module) {

        $field = $this->_get_field($name, $module);
        if (!$field) {
            return $value;
        }

        $options = dr_format_option_array($field['setting']['option']['options']);
        if ($options && isset($options[$value])) {
            return $options[$value];
        }

        return $value;
    }

    // 原样输出
    public function get_value($name, $value, $module) {
        return $value;
    }

    // 下拉字段name值
    public function select_name($name, $value, $module) {
        return $this->radio_name($name, $value, $module);
    }

    // checkbox字段name值
    public function checkbox_name($name, $value, $module) {

        $field = $this->_get_field($name, $module);
        if (!$field) {
            return $value;
        }

        $arr = dr_string2array($value);
        if (is_array($arr)) {
            $options = dr_format_option_array($field['setting']['option']['options']);
            if ($options) {
                $rt = [];
                foreach ($options as $i => $v) {
                    if (dr_in_array($i, $arr)) {
                        $rt[] = $v;
                    }
                }
                return implode('、', $rt);
            }
        }

        return $value;
    }

    // 联动字段name值
    public function linkage_name($name, $value, $module) {

        if (!$value) {
            return '';
        }

        $field = $this->_get_field($name, $module);
        if (!$field) {
            return $value;
        }

        if ($field['setting']['option']['linkage']) {
            return dr_linkagepos($field['setting']['option']['linkage'], $value, '-');
        }

        return $value;
    }

    // 联动多项字段name值
    public function linkages_name($name, $value, $module) {

        if (!$value) {
            return '';
        }

        $field = $this->_get_field($name, $module);
        if (!$field) {
            return $value;
        }

        if ($field['setting']['option']['linkage']) {
            $rt = [];
            $values = dr_string2array($value);
            foreach ($values as $value) {
                $rt[] = dr_linkagepos($field['setting']['option']['linkage'], $value, '-');
            }
            return implode('、', $rt);
        }

        return $value;
    }
}

