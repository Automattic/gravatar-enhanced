<?php

namespace Automattic\Gravatar\PHPStanExtensions;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\ShouldNotHappenException;

/**
 * Extension for PHPStan to support array-merge custom type.
 */
class ArrayMergeTypeNodeResolverExtension implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension {

	/**
	 * @var TypeNodeResolver
	 */
	private $type_node_resolver;

	/**
	 * @param TypeNodeResolver $type_node_resolver
	 *
	 * @return void
	 */
	public function setTypeNodeResolver( $type_node_resolver ): void {
		$this->type_node_resolver = $type_node_resolver;
	}

	/**
	 * @param TypeNode $type_node
	 * @param NameScope $name_scope
	 *
	 * @return Type|null
	 * @throws ShouldNotHappenException
	 */
	public function resolve( $type_node, $name_scope ): ?Type {
		if ( ! $type_node instanceof GenericTypeNode ) {
			return null;
		}

		$type_name = $type_node->type;
		if ( $type_name->name !== 'array-merge' ) {
			return null;
		}

		$argument_types = array_map(
			fn( $argument ) => $this->type_node_resolver->resolve( $argument, $name_scope ),
			$type_node->genericTypes
		);

		$merged_type_builder = ConstantArrayTypeBuilder::createEmpty();
		foreach ( $argument_types as $argument_type ) {
			if ( $argument_type instanceof ConstantArrayType ) {
				$constant_arrays = $argument_type->getConstantArrays();
				foreach ( $constant_arrays as $constant_array ) {
					foreach ( $constant_array->getKeyTypes() as $i => $keyType ) {
						$valueType = $constant_array->getValueTypes()[ $i ];
						$merged_type_builder->setOffsetValueType(
							$keyType,
							$valueType,
							$constant_array->isOptionalKey( $i ),
						);
					}
				}
			} elseif ( $argument_type instanceof ArrayType ) {
				$arrays = $argument_type->getArrays();
				foreach ( $arrays as $array ) {
					$key_type  = $array->getKeyType();
					$item_type = $array->getItemType();

					if ( $key_type instanceof UnionType ) {
						$types = $key_type->getTypes();
						foreach ( $types as $type ) {
							$merged_type_builder->setOffsetValueType(
								$type,
								$item_type,
								true,
							);
						}
					} else {
						$merged_type_builder->setOffsetValueType(
							$key_type,
							$item_type,
							true,
						);
					}
				}
			} elseif ( $argument_type instanceof ConstantStringType ) {
				$merged_type_builder->setOffsetValueType(
					new StringType(),
					$argument_type,
				);
			} elseif ( $argument_type instanceof UnionType ) {
				$types = $argument_type->getTypes();
				foreach ( $types as $type ) {
					if ( $type instanceof ConstantStringType ) {
						$merged_type_builder->setOffsetValueType(
							new StringType(),
							$type,
						);
					}
				}
			} else {
				throw new ShouldNotHappenException( 'array-merge only supports array types or phpstan type that resolve to arrays ' );
			}
		}

		return $merged_type_builder->getArray();
	}
}
