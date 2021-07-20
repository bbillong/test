<?php
//insert select duplicate example
$sql = "
INSERT INTO insert_table
            (
                idx,
                corp_code,
                employee_code,
                employee_name,
                work_date,
                staff_salary_idx,
                month_calculate_idx,
                depart_code,
                depart_name,
                grade_code,
                grade_name,
                position_code,
                position_name,
                price_base,
                price_allowance,
                price_severance,
                price_deduction,
                state,
                earned_tax_number,
                earned_tax_idx,
                price_earned_tax,
                work_day,
                work_day_personal,
                type_price_base,
                price_week_extension_allowance,
                price_week_night_allowance,
                price_extension_allowance,
                price_night_allowance,
                price_week_allowance,
                price_ei_total,
                price_ei_personal,
                price_nhis_total,
                price_nhis_personal,
                price_nps_total,
                price_nps_personal,
                reg_datetime,
                reg_mbi_id
            )
            SELECT
                sc.idx,
                sc.corp_code,
                sc.employee_code,
                sc.employee_name,
                sc.work_date,
                sc.staff_salary_idx,
                sc.month_calculate_idx,
                sc.depart_code,
                sc.depart_name,
                sc.grade_code,
                sc.grade_name,
                sc.position_code,
                sc.position_name,
                sc.price_base,
                sc.price_allowance,
                sc.price_severance,
                sc.price_deduction,
                sc.state,
                sc.earned_tax_number,
                et.idx AS earned_tax_idx,
                (
                    CASE
                        WHEN sc.earned_tax_number = 1 then et.family_1
                        WHEN sc.earned_tax_number = 2 then et.family_2
                        WHEN sc.earned_tax_number = 3 then et.family_3
                        WHEN sc.earned_tax_number = 4 then et.family_4
                        WHEN sc.earned_tax_number = 5 then et.family_5
                        WHEN sc.earned_tax_number = 6 then et.family_6
                        WHEN sc.earned_tax_number = 7 then et.family_7
                        WHEN sc.earned_tax_number = 8 then et.family_8
                        WHEN sc.earned_tax_number = 9 then et.family_9
                        WHEN sc.earned_tax_number = 10 then et.family_10
                        WHEN sc.earned_tax_number = 11 then et.family_11
                    END
                ) AS price_earned_tax,
                sc.work_day,
                sc.work_day_personal,
                sc.type_price_base,
                sc.price_week_extension_allowance,
                sc.price_week_night_allowance,
                sc.price_extension_allowance,
                sc.price_night_allowance,
                sc.price_week_allowance,
                sc.price_ei_total,
                sc.price_ei_personal,
                sc.price_nhis_total,
                sc.price_nhis_personal,
                sc.price_nps_total,
                sc.price_nps_personal,
                sc.reg_datetime,
                sc.reg_mbi_id
            FROM first_table AS sc
            INNER JOIN second_table AS et
                ON (sc.corp_code = et.corp_code
                AND et.price_more <= ((sc.price_base
						                + IF(sc.price_allowance, sc.price_allowance, 0)
						                + IF(sc.price_week_extension_allowance, sc.price_week_extension_allowance, 0)
						                + IF(sc.price_week_night_allowance, sc.price_week_night_allowance, 0)
						                + IF(sc.price_extension_allowance, sc.price_extension_allowance, 0)
						                + IF(sc.price_night_allowance, sc.price_night_allowance, 0)
						                + IF(sc.price_week_allowance, sc.price_week_allowance, 0)
						                - IF(sc.price_deduction, sc.price_deduction, 0)
						                - IF(sc.price_ei_personal, sc.price_ei_personal, 0)
						                - IF(sc.price_nhis_personal, sc.price_nhis_personal, 0)
						                - IF(sc.price_nps_personal, sc.price_nps_personal, 0)) / 1000)
                AND et.price_less > ((sc.price_base
						                + IF(sc.price_allowance, sc.price_allowance, 0)
						                + IF(sc.price_week_extension_allowance, sc.price_week_extension_allowance, 0)
						                + IF(sc.price_week_night_allowance, sc.price_week_night_allowance, 0)
						                + IF(sc.price_extension_allowance, sc.price_extension_allowance, 0)
						                + IF(sc.price_night_allowance, sc.price_night_allowance, 0)
						                + IF(sc.price_week_allowance, sc.price_week_allowance, 0)
						                - IF(sc.price_deduction, sc.price_deduction, 0)
						                - IF(sc.price_ei_personal, sc.price_ei_personal, 0)
						                - IF(sc.price_nhis_personal, sc.price_nhis_personal, 0)
						                - IF(sc.price_nps_personal, sc.price_nps_personal, 0)) / 1000)
                AND et.sort = 'sort')
            WHERE sc.corp_code = 'corp'
            AND sc.work_date = 'date'
            AND sc.employee_code = 'employee'
            ON DUPLICATE KEY UPDATE
                earned_tax_idx = et.idx,
                price_earned_tax = (
                                    CASE
                                        WHEN sc.earned_tax_number = 1 then et.family_1
                                        WHEN sc.earned_tax_number = 2 then et.family_2
                                        WHEN sc.earned_tax_number = 3 then et.family_3
                                        WHEN sc.earned_tax_number = 4 then et.family_4
                                        WHEN sc.earned_tax_number = 5 then et.family_5
                                        WHEN sc.earned_tax_number = 6 then et.family_6
                                        WHEN sc.earned_tax_number = 7 then et.family_7
                                        WHEN sc.earned_tax_number = 8 then et.family_8
                                        WHEN sc.earned_tax_number = 9 then et.family_9
                                        WHEN sc.earned_tax_number = 10 then et.family_10
                                        WHEN sc.earned_tax_number = 11 then et.family_11
                                    END
                                ),
                mod_datetime = 'now',
                mod_mbi_id = 'id'
";