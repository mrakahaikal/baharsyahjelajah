<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Pengesahan
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut mengandungi mesej ralat lalai yang digunakan oleh
    | kelas validator. Beberapa peraturan ini mempunyai beberapa versi seperti
    | peraturan saiz. Jangan ragu untuk mengubah setiap mesej ini di sini.
    |
    */

    'accepted' => 'Ruangan :attribute mesti diterima.',
    'accepted_if' => 'Ruangan :attribute mesti diterima apabila :other adalah :value.',
    'active_url' => 'Ruangan :attribute mestilah URL yang sah.',
    'after' => 'Ruangan :attribute mestilah tarikh selepas :date.',
    'after_or_equal' => 'Ruangan :attribute mestilah tarikh selepas atau sama dengan :date.',
    'alpha' => 'Ruangan :attribute hanya boleh mengandungi huruf.',
    'alpha_dash' => 'Ruangan :attribute hanya boleh mengandungi huruf, nombor, sengkang, dan garis bawah.',
    'alpha_num' => 'Ruangan :attribute hanya boleh mengandungi huruf dan nombor.',
    'any_of' => 'Ruangan :attribute tidak sah.',
    'array' => 'Ruangan :attribute mestilah sebuah tatasusunan (array).',
    'ascii' => 'Ruangan :attribute hanya boleh mengandungi karakter alfanumerik dan simbol bait tunggal.',
    'before' => 'Ruangan :attribute mestilah tarikh sebelum :date.',
    'before_or_equal' => 'Ruangan :attribute mestilah tarikh sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Ruangan :attribute mesti mempunyai antara :min dan :max item.',
        'file' => 'Ruangan :attribute mestilah antara :min dan :max kilobait.',
        'numeric' => 'Ruangan :attribute mestilah antara :min dan :max.',
        'string' => 'Ruangan :attribute mestilah antara :min dan :max aksara.',
    ],
    'boolean' => 'Ruangan :attribute mestilah benar atau salah.',
    'can' => 'Ruangan :attribute mengandungi nilai yang tidak dibenarkan.',
    'confirmed' => 'Pengesahan :attribute tidak sepadan.',
    'contains' => 'Ruangan :attribute kehilangan nilai yang diperlukan.',
    'current_password' => 'Kata laluan adalah salah.',
    'date' => 'Ruangan :attribute mestilah tarikh yang sah.',
    'date_equals' => 'Ruangan :attribute mestilah tarikh yang sama dengan :date.',
    'date_format' => 'Ruangan :attribute mesti sepadan dengan format :format.',
    'decimal' => 'Ruangan :attribute mesti mempunyai :decimal tempat perpuluhan.',
    'declined' => 'Ruangan :attribute mesti ditolak.',
    'declined_if' => 'Ruangan :attribute mesti ditolak apabila :other adalah :value.',
    'different' => 'Ruangan :attribute dan :other mestilah berbeza.',
    'digits' => 'Ruangan :attribute mestilah :digits digit.',
    'digits_between' => 'Ruangan :attribute mestilah antara :min dan :max digit.',
    'dimensions' => 'Ruangan :attribute mempunyai dimensi imej yang tidak sah.',
    'distinct' => 'Ruangan :attribute mempunyai nilai pendua.',
    'doesnt_contain' => 'Ruangan :attribute tidak boleh mengandungi mana-mana yang berikut: :values.',
    'doesnt_end_with' => 'Ruangan :attribute tidak boleh diakhiri dengan salah satu daripada yang berikut: :values.',
    'doesnt_start_with' => 'Ruangan :attribute tidak boleh dimulakan dengan salah satu daripada yang berikut: :values.',
    'email' => 'Ruangan :attribute mestilah alamat emel yang sah.',
    'encoding' => 'Ruangan :attribute mesti dikodkan dalam :encoding.',
    'ends_with' => 'Ruangan :attribute mesti diakhiri dengan salah satu daripada yang berikut: :values.',
    'enum' => ':Attribute yang dipilih tidak sah.',
    'exists' => ':Attribute yang dipilih tidak sah.',
    'extensions' => 'Ruangan :attribute mesti mempunyai salah satu sambungan berikut: :values.',
    'file' => 'Ruangan :attribute mestilah sebuah fail.',
    'filled' => 'Ruangan :attribute mesti mempunyai nilai.',
    'gt' => [
        'array' => 'Ruangan :attribute mesti mempunyai lebih daripada :value item.',
        'file' => 'Ruangan :attribute mestilah lebih besar daripada :value kilobait.',
        'numeric' => 'Ruangan :attribute mestilah lebih besar daripada :value.',
        'string' => 'Ruangan :attribute mestilah lebih besar daripada :value aksara.',
    ],
    'gte' => [
        'array' => 'Ruangan :attribute mesti mempunyai :value item atau lebih.',
        'file' => 'Ruangan :attribute mestilah lebih besar daripada atau sama dengan :value kilobait.',
        'numeric' => 'Ruangan :attribute mestilah lebih besar daripada atau sama dengan :value.',
        'string' => 'Ruangan :attribute mestilah lebih besar daripada atau sama dengan :value aksara.',
    ],
    'hex_color' => 'Ruangan :attribute mestilah warna heksadesimal yang sah.',
    'image' => 'Ruangan :attribute mestilah sebuah imej.',
    'in' => ':Attribute yang dipilih tidak sah.',
    'in_array' => 'Ruangan :attribute mesti wujud dalam :other.',
    'in_array_keys' => 'Ruangan :attribute mesti mengandungi sekurang-kurangnya satu daripada kunci berikut: :values.',
    'integer' => 'Ruangan :attribute mestilah integer.',
    'ip' => 'Ruangan :attribute mestilah alamat IP yang sah.',
    'ipv4' => 'Ruangan :attribute mestilah alamat IPv4 yang sah.',
    'ipv6' => 'Ruangan :attribute mestilah alamat IPv6 yang sah.',
    'json' => 'Ruangan :attribute mestilah rentetan JSON yang sah.',
    'list' => 'Ruangan :attribute mestilah sebuah senarai.',
    'lowercase' => 'Ruangan :attribute mestilah huruf kecil.',
    'lt' => [
        'array' => 'Ruangan :attribute mesti mempunyai kurang daripada :value item.',
        'file' => 'Ruangan :attribute mestilah kurang daripada :value kilobait.',
        'numeric' => 'Ruangan :attribute mestilah kurang daripada :value.',
        'string' => 'Ruangan :attribute mestilah kurang daripada :value aksara.',
    ],
    'lte' => [
        'array' => 'Ruangan :attribute tidak boleh mempunyai lebih daripada :value item.',
        'file' => 'Ruangan :attribute mestilah kurang daripada atau sama dengan :value kilobait.',
        'numeric' => 'Ruangan :attribute mestilah kurang daripada atau sama dengan :value.',
        'string' => 'Ruangan :attribute mestilah kurang daripada atau sama dengan :value aksara.',
    ],
    'mac_address' => 'Ruangan :attribute mestilah alamat MAC yang sah.',
    'max' => [
        'array' => 'Ruangan :attribute tidak boleh mempunyai lebih daripada :max item.',
        'file' => 'Ruangan :attribute tidak boleh lebih besar daripada :max kilobait.',
        'numeric' => 'Ruangan :attribute tidak boleh lebih besar daripada :max.',
        'string' => 'Ruangan :attribute tidak boleh lebih besar daripada :max aksara.',
    ],
    'max_digits' => 'Ruangan :attribute tidak boleh mempunyai lebih daripada :max digit.',
    'mimes' => 'Ruangan :attribute mestilah fail jenis: :values.',
    'mimetypes' => 'Ruangan :attribute mestilah fail jenis: :values.',
    'min' => [
        'array' => 'Ruangan :attribute mesti mempunyai sekurang-kurangnya :min item.',
        'file' => 'Ruangan :attribute mestilah sekurang-kurangnya :min kilobait.',
        'numeric' => 'Ruangan :attribute mestilah sekurang-kurangnya :min.',
        'string' => 'Ruangan :attribute mestilah sekurang-kurangnya :min aksara.',
    ],
    'min_digits' => 'Ruangan :attribute mesti mempunyai sekurang-kurangnya :min digit.',
    'missing' => 'Ruangan :attribute mesti hilang.',
    'missing_if' => 'Ruangan :attribute mesti hilang apabila :other adalah :value.',
    'missing_unless' => 'Ruangan :attribute mesti hilang melainkan :other adalah :value.',
    'missing_with' => 'Ruangan :attribute mesti hilang apabila :values wujud.',
    'missing_with_all' => 'Ruangan :attribute mesti hilang apabila :values wujud.',
    'multiple_of' => 'Ruangan :attribute mestilah gandaan :value.',
    'not_in' => ':Attribute yang dipilih tidak sah.',
    'not_regex' => 'Format ruangan :attribute tidak sah.',
    'numeric' => 'Ruangan :attribute mestilah nombor.',
    'password' => [
        'letters' => 'Ruangan :attribute mesti mengandungi sekurang-kurangnya satu huruf.',
        'mixed' => 'Ruangan :attribute mesti mengandungi sekurang-kurangnya satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Ruangan :attribute mesti mengandungi sekurang-kurangnya satu nombor.',
        'symbols' => 'Ruangan :attribute mesti mengandungi sekurang-kurangnya satu simbol.',
        'uncompromised' => ':Attribute yang diberikan telah muncul dalam kebocoran data. Sila pilih :attribute yang lain.',
    ],
    'present' => 'Ruangan :attribute mesti wujud.',
    'present_if' => 'Ruangan :attribute mesti wujud apabila :other adalah :value.',
    'present_unless' => 'Ruangan :attribute mesti wujud melainkan :other adalah :value.',
    'present_with' => 'Ruangan :attribute mesti wujud apabila :values wujud.',
    'present_with_all' => 'Ruangan :attribute mesti wujud apabila :values wujud.',
    'prohibited' => 'Ruangan :attribute dilarang.',
    'prohibited_if' => 'Ruangan :attribute dilarang apabila :other adalah :value.',
    'prohibited_if_accepted' => 'Ruangan :attribute dilarang apabila :other diterima.',
    'prohibited_if_declined' => 'Ruangan :attribute dilarang apabila :other ditolak.',
    'prohibited_unless' => 'Ruangan :attribute dilarang melainkan :other berada dalam :values.',
    'prohibits' => 'Ruangan :attribute melarang :other daripada wujud.',
    'regex' => 'Format ruangan :attribute tidak sah.',
    'required' => 'Ruangan :attribute diperlukan.',
    'required_array_keys' => 'Ruangan :attribute mesti mengandungi entri untuk: :values.',
    'required_if' => 'Ruangan :attribute diperlukan apabila :other adalah :value.',
    'required_if_accepted' => 'Ruangan :attribute diperlukan apabila :other diterima.',
    'required_if_declined' => 'Ruangan :attribute diperlukan apabila :other ditolak.',
    'required_unless' => 'Ruangan :attribute diperlukan melainkan :other berada dalam :values.',
    'required_with' => 'Ruangan :attribute diperlukan apabila :values wujud.',
    'required_with_all' => 'Ruangan :attribute diperlukan apabila :values wujud.',
    'required_without' => 'Ruangan :attribute diperlukan apabila :values tidak wujud.',
    'required_without_all' => 'Ruangan :attribute diperlukan apabila tiada :values yang wujud.',
    'same' => 'Ruangan :attribute mesti sepadan dengan :other.',
    'size' => [
        'array' => 'Ruangan :attribute mesti mengandungi :size item.',
        'file' => 'Ruangan :attribute mestilah :size kilobait.',
        'numeric' => 'Ruangan :attribute mestilah :size.',
        'string' => 'Ruangan :attribute mestilah :size aksara.',
    ],
    'starts_with' => 'Ruangan :attribute mesti dimulakan dengan salah satu daripada yang berikut: :values.',
    'string' => 'Ruangan :attribute mestilah rentetan (string).',
    'timezone' => 'Ruangan :attribute mestilah zon masa yang sah.',
    'unique' => ':Attribute telah pun diambil.',
    'uploaded' => ':Attribute gagal dimuat naik.',
    'uppercase' => 'Ruangan :attribute mestilah huruf besar.',
    'url' => 'Ruangan :attribute mestilah URL yang sah.',
    'ulid' => 'Ruangan :attribute mestilah ULID yang sah.',
    'uuid' => 'Ruangan :attribute mestilah UUID yang sah.',

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Pengesahan Khas
    |--------------------------------------------------------------------------
    |
    | Di sini anda boleh tentukan mesej pengesahan khas untuk atribut menggunakan
    | konvensyen "attribute.rule" untuk menamakan baris. Ini memudahkan untuk
    | tentukan baris bahasa khas tertentu untuk peraturan atribut tertentu.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atribut Pengesahan Khas
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk menukar tempat letak atribut kami
    | dengan sesuatu yang lebih mesra pembaca seperti "Alamat Emel" dan bukannya
    | "email". Ini membantu kami menjadikan mesej kami lebih ekspresif.
    |
    */

    'attributes' => [],

];
